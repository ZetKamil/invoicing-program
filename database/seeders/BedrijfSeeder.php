<?php

namespace Database\Seeders;

use App\Models\Bedrijf;
use Illuminate\Database\Seeder;

class BedrijfSeeder extends Seeder
{
    public function run(): void
    {
        Bedrijf::create([
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
