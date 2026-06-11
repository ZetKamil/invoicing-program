<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BedrijfSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            LeadQuoteSeeder::class,
            InvoiceSeeder::class,
            ClientDemoSeeder::class,
        ]);
    }
}
