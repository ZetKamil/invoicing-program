<?php

namespace App\Http\Controllers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    /**
     * Handle exiting impersonation.
     * Bypasses the TenantScope to find the Super Admin,
     * and manually swaps the session ID to bypass AuthenticateSession logout.
     */
    public function leave(Request $request)
    {
        if (session()->has('impersonated_by')) {
            $superAdminId = session('impersonated_by');
            
            // Bypass TenantScope to find the Super Admin in the DB
            $superAdmin = User::withoutGlobalScopes()->find($superAdminId);
            
            if ($superAdmin) {
                // Manually determine the current guard and its name
                $guardName = Filament::getCurrentOrDefaultPanel()?->getAuthGuard() ?? 'web';
                $sessionGuard = auth()->guard($guardName);
                
                // Clear the client's auth data from the session
                session()->forget($sessionGuard->getName());
                session()->forget($sessionGuard->getRecallerName());
                
                // Clear the package's impersonation flags
                session()->forget([
                    'impersonated_by',
                    'impersonator_guard',
                    'impersonator_guard_using',
                    'remember_web',
                ]);

                // Manually inject the Super Admin's ID into the session 
                // This correctly avoids triggering a password_hash check failure in AuthenticateSession!
                session()->put($sessionGuard->getName(), $superAdmin->getAuthIdentifier());
                
                // Also clear the password_hash from the session so the middleware 
                // re-caches the Super Admin's hash instead of complaining about a mismatch.
                session()->forget('password_hash_' . $guardName);

                // Force the auth system to reload the user object from the session on the next request
                auth()->forgetGuards();
            } else {
                \Illuminate\Support\Facades\Log::error("Impersonation Exit Failed: Super Admin not found for ID: {$superAdminId}");
            }

            return redirect(session()->pull('impersonate.back_to', '/dashboard'));
        }

        return redirect('/dashboard');
    }
}
