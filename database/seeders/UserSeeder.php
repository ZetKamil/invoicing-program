<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'logi-web-pro')->first();

        if ($tenant) {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Admin Logi-Web',
                'email' => 'admin@logiweb.pro',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
            ]);
        }
    }
}
