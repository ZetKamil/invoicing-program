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
            $user = User::firstOrCreate(
                ['email' => 'admin@logiweb.pro'],
                [
                    'name' => 'Kamil (Super Admin)',
                    'password' => Hash::make('password'),
                    'is_super_admin' => true,
                ]
            );

            $tenant->users()->syncWithoutDetaching([
                $user->id => [
                    'id' => (string) \Illuminate\Support\Str::ulid(),
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}
