<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Bedrijf;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bedrijf = Bedrijf::create([
            'name' => 'Logi-Web PRO',
            'slug' => 'logi-web-pro',
            'settings' => [
                'theme' => 'dark',
                'package' => 'agency',
            ],
        ]);

        User::create([
            'bedrijf_id' => $bedrijf->id,
            'name' => 'Admin Logi-Web',
            'email' => 'admin@logiweb.pro',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);
                User::create([
            'bedrijf_id' => $bedrijf->id,
            'name' => 'Admin Logi-Web',
            'email' => 'kamil@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);
    }
}
