<?php

namespace Database\Seeders;

use App\Actions\Quotes\CreateQuoteAction;
use App\Models\Lead;
use App\Models\Bedrijf;
use Illuminate\Database\Seeder;

class LeadQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $bedrijf = Bedrijf::where('slug', 'logi-web-pro')->first();

        if ($bedrijf) {
            $leads = Lead::factory()->count(5)->create([
                'bedrijf_id' => $bedrijf->id,
            ]);

            $createQuoteAction = new CreateQuoteAction();

            foreach ($leads as $lead) {
                // Demo logic: Total amount generated between 500 and 2000
                $createQuoteAction->handle($lead, rand(500, 2000) + 0.99, 14);
            }
        }
    }
}
