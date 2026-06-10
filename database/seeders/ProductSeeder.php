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
                'name' => 'Webdesign: Basic Setup',
                'description' => 'One-time setup for basic webdesign.',
                'price' => 850.00,
                'type' => ProductType::SERVICE,
                'is_recurring' => false,
            ]);

            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Webdesign: Pro Transport Edition',
                'description' => 'One-time setup with mini-CRM for logistics.',
                'price' => 2450.00,
                'type' => ProductType::SERVICE,
                'is_recurring' => false,
            ]);

            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Master-Digit: Software Licentie & Support',
                'description' => 'Yearly SaaS license / equivalent to €50/mo MRR.',
                'price' => 600.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => true,
            ]);
            
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Master-Digit: Premium Modules & API',
                'description' => 'Yearly advanced e-invoicing extension.',
                'price' => 1188.00,
                'type' => ProductType::LICENSE,
                'is_recurring' => true,
            ]);
        }
    }
}
