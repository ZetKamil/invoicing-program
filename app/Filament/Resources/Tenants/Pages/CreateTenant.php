<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

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
        \App\Models\User::create([
            'tenant_id' => $this->record->id,
            'name' => 'Admin ' . $this->record->name,
            'email' => $this->adminEmail,
            'password' => \Illuminate\Support\Facades\Hash::make($this->adminPassword),
            'role' => \App\Enums\UserRole::ADMIN,
        ]);

        // 2. Insert this new tenant as a Lead inside Logi-Web PRO (Tenant #1)
        $superAdminTenant = \App\Models\Tenant::where('slug', 'logi-web-pro')->first();
        if ($superAdminTenant) {
            \App\Models\Lead::create([
                'tenant_id' => $superAdminTenant->id,
                'company_name' => $this->record->name,
                'contact_person' => 'Admin ' . $this->record->name,
                'email' => $this->adminEmail,
                'status' => \App\Enums\LeadStatus::NEW,
            ]);
        }
    }
}
