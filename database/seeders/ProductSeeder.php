<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'logi-web-pro')->first();

        if ($tenant) {
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Package 1: Start',
                'description' => 'Start package for new businesses.',
                'price' => 99.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => true,
            ]);

            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Package 2: Pro',
                'description' => 'Pro package for growing businesses.',
                'price' => 199.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => true,
            ]);

            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Package 3: Enterprise',
                'description' => 'Enterprise package for large organizations. Contact us.',
                'price' => 0.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => true,
            ]);
        }
    }
}
