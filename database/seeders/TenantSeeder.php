<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'name' => 'Logi-Web PRO',
            'slug' => 'logi-web-pro',
            'stripe_subscription_status' => 'active',
            'settings' => [
                'currency' => 'EUR',
                'contact_info' => 'contact@logiweb.pro',
                'theme' => 'dark',
            ],
        ]);
    }
}
