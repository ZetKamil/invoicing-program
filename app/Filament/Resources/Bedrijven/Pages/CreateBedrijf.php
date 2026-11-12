<?php

namespace App\Filament\Resources\Bedrijven\Pages;

use App\Filament\Resources\Bedrijven\BedrijfResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBedrijf extends CreateRecord
{
    protected static string $resource = BedrijfResource::class;

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
        // 1. Create primary Admin User for the new Bedrijf
        \App\Models\User::create([
            'bedrijf_id' => $this->record->id,
            'name' => 'Admin ' . $this->record->name,
            'email' => $this->adminEmail,
            'password' => \Illuminate\Support\Facades\Hash::make($this->adminPassword),
            'role' => \App\Enums\UserRole::ADMIN,
        ]);

        // 2. Insert this new bedrijf as a Lead inside Logi-Web PRO (Bedrijf #1)
        $superAdminBedrijf = \App\Models\Bedrijf::where('slug', 'logi-web-pro')->first();
        if ($superAdminBedrijf) {
            \App\Models\Lead::create([
                'bedrijf_id' => $superAdminBedrijf->id,
                'company_name' => $this->record->name,
                'contact_person' => 'Admin ' . $this->record->name,
                'email' => $this->adminEmail,
                'status' => \App\Enums\LeadStatus::NEW,
            ]);
        }
    }
}
