<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'logi-web-pro'],
            [
                'name' => 'Logi-Web PRO',
                'status' => 'active',
                'settings' => [
                    'theme' => 'dark',
                    'package' => 'agency',
                ],
            ]
        );

        $user1 = User::firstOrCreate(
            ['email' => 'admin@logiweb.pro'],
            [
                'name' => 'Admin Logi-Web',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'kamil@test.com'],
            [
                'name' => 'Admin Logi-Web',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
            ]
        );

        $tenant->users()->syncWithoutDetaching([
            $user1->id => [
                'id' => (string) Str::ulid(),
                'role' => 'owner',
            ],
            $user2->id => [
                'id' => (string) Str::ulid(),
                'role' => 'admin',
            ],
        ]);
    }
}
