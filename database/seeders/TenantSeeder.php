<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::updateOrCreate(
            ['slug' => 'logi-web-pro'],
            [
                'name' => 'Logi-Web PRO',
                'vat_number' => 'BE0789123456',
                'peppol_id' => '0088:789123456',
                'settings' => [
                    'currency' => 'EUR',
                    'contact_email' => 'hello@logiweb.pro',
                    'contact_phone' => '+32 470 00 00 00',
                    'address' => 'Transportlaan 1, 8000 Brugge',
                ],
            ]
        );
    }
}
