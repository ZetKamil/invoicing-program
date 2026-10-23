<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected ?string $adminEmail = null;
    protected ?string $adminPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->adminEmail = $data['admin_email'];
        $this->adminPassword = $data['admin_password'];
        
        unset($data['admin_email'], $data['admin_password']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // 1. Create primary Admin User for the new Tenant
        $user = User::create([
            'name' => 'Admin ' . $this->record->name,
            'email' => $this->adminEmail,
            'password' => Hash::make($this->adminPassword),
            'role' => \App\Enums\UserRole::ADMIN,
        ]);

        $this->record->users()->syncWithoutDetaching([
            $user->id => [
                'id' => (string) Str::ulid(),
                'role' => 'owner',
            ],
        ]);

        // 2. Insert this new tenant as a Lead inside Logi-Web PRO (Agency Tenant #1)
        $superAdminTenant = Tenant::where('slug', 'logi-web-pro')->first();
        if ($superAdminTenant) {
            Lead::create([
                'tenant_id' => $superAdminTenant->id,
                'company_name' => $this->record->name,
                'contact_person' => 'Admin ' . $this->record->name,
                'email' => $this->adminEmail,
                'status' => \App\Enums\LeadStatus::NEW,
            ]);
        }
    }
}
