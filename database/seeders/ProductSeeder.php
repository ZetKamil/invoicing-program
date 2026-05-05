<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
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

        $packages = [
            [
                'name' => 'Package 1: Start',
                'description' => 'Professionele one-pager, Basis offerteformulier, SEO-optimalisatie, Hosting inbegrepen',
                'price' => 850.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => false,
            ],
            [
                'name' => 'Package 2: Pro',
                'description' => '5 pagina\'s op maat, Smart Quote System, Mini-CRM dashboard, Live in 7 werkdagen',
                'price' => 1150.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => false,
            ],
            [
                'name' => 'Package 3: Enterprise',
                'description' => 'Volledige automatisering, E-facturatiemodule, Chauffeursbeheer, TMS-uitbreiding',
                'price' => 0.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => false,
            ],
        ];

        foreach ($packages as $package) {
            Product::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $package['name'],
                ],
                $package
            );
        }
    }
}
