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
        // 1. Platform configuratie (De SaaS-eigenaar / SuperAdmin)
        $this->call([
            BedrijfSeeder::class,
            UserSeeder::class,
        ]);

        // 2. Multi-Tenant Demo Data (4 logistieke bedrijven met facturen, leads en producten)
        $this->call([
            ClientDemoSeeder::class,
        ]);
    }
}
