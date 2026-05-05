<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'logi-web-pro')->first();

        if (!$tenant) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@logiweb.pro'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Primary Admin',
                'password' => Hash::make('password'), // Use a secure password in production
                'role' => UserRole::ADMIN,
                'email_verified_at' => now(),
            ]
        );
    }
}
