<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['slug' => 'logi-web-pro'],
            [
                'name' => 'Logi-Web PRO',
                'status' => 'active',
                'settings' => [
                    'currency' => 'EUR',
                    'contact_info' => 'contact@logiweb.pro',
                    'theme' => 'dark',
                ],
            ]
        );
    }
}
