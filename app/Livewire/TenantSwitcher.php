<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Component;
use STS\FilamentImpersonate\Facades\Impersonation;

class TenantSwitcher extends Component
{
    /**
     * Impersonate the primary admin of the given tenant.
     * Called directly by wire:click from the Filament dropdown list item.
     */
    public function impersonateTenant(string $tenantId): void
    {
        $currentUser = Filament::auth()->user();

        // Only super-admins may switch tenants
        if (! $currentUser || ! $currentUser->is_super_admin) {
            return;
        }

        // Prevent nested impersonation
        if (Impersonation::isImpersonating()) {
            return;
        }

        if (blank($tenantId)) {
            return;
        }

        // Resolve the first admin/dispatcher user for the target tenant
        $targetUser = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'dispatcher'])
            ->orderBy('created_at')
            ->first();

        if (! $targetUser) {
            return;
        }

        $panel = Filament::getCurrentOrDefaultPanel();
        $guard = $panel->getAuthGuard();

        // Store return URL so the "leave impersonation" action knows where to go back
        session()->put([
            'impersonate.back_to' => $panel->getUrl(),
            'impersonate.guard'   => $guard,
        ]);

        if (Impersonation::enter($currentUser, $targetUser, $guard)) {
            $this->redirect($panel->getUrl(), navigate: false);
        }
    }

    /**
     * Leave the current impersonation session and return to the super-admin panel.
     */
    public function leaveImpersonation(): void
    {
        if (session()->has('impersonated_by')) {
            $superAdminId = session('impersonated_by');
            \Illuminate\Support\Facades\Log::info("Leaving impersonation. Returning to Super Admin ID: {$superAdminId}");
            
            // Clear all package session flags
            session()->forget([
                'impersonated_by',
                'impersonator_guard',
                'impersonator_guard_using',
                'remember_web',
            ]);
            
            $superAdmin = User::withoutGlobalScopes()->find($superAdminId);
            
            if ($superAdmin) {
                // Perform a native login using the exact guard configured for Filament
                $guard = Filament::getCurrentOrDefaultPanel()->getAuthGuard();
                \Illuminate\Support\Facades\Log::info("Logging in Super Admin to guard: {$guard}");
                auth()->guard($guard)->login($superAdmin);
                session()->save(); // Force save to persist auth state before Livewire redirect
            } else {
                \Illuminate\Support\Facades\Log::error("Super Admin not found for ID: {$superAdminId}");
            }

            $this->redirect(
                session()->pull('impersonate.back_to', Filament::getCurrentOrDefaultPanel()->getUrl()),
                navigate: false
            );
        } else {
            \Illuminate\Support\Facades\Log::warning("leaveImpersonation called but session has no impersonated_by key.");
        }
    }

    public function render()
    {
        $isImpersonating = Impersonation::isImpersonating();
        $isSuperAdmin    = auth()->user()?->is_super_admin ?? false;

        // Only load tenant list when needed – avoid unnecessary DB queries for regular users
        $tenants = ($isSuperAdmin && ! $isImpersonating)
            ? Tenant::orderBy('name')->get(['id', 'name'])
            : collect();

        return view('livewire.tenant-switcher', compact('tenants', 'isImpersonating', 'isSuperAdmin'));
    }
}
