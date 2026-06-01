<?php

namespace Database\Seeders;

use App\Actions\Quotes\CreateQuoteAction;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class LeadQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'logi-web-pro')->first();

        if ($tenant) {
            $leads = Lead::factory()->count(5)->create([
                'tenant_id' => $tenant->id,
            ]);

            $createQuoteAction = new CreateQuoteAction();

            foreach ($leads as $lead) {
                // Demo logic: Total amount generated between 500 and 2000
                $createQuoteAction->handle($lead, rand(500, 2000) + 0.99, 14);
            }
        }
    }
}
