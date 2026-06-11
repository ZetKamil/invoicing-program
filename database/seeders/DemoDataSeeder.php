<?php

namespace Database\Seeders;

use App\Actions\Quotes\CreateQuoteAction;
use App\Models\Lead;
use App\Models\Bedrijf;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bedrijf = Bedrijf::where('slug', 'logi-web-pro')->first();
        $admin = User::where('email', 'admin@logiweb.pro')->first();

        if (!$bedrijf || !$admin) {
            return;
        }

        // Authenticate as admin so HasBedrijf trait works correctly for the action
        Auth::login($admin);

        // Create Demo Leads for Logi-Web PRO
        $leads = Lead::factory()->count(10)->create([
            'bedrijf_id' => $bedrijf->id,
        ]);

        // Create Demo Quotes using the CreateQuoteAction
        $createQuoteAction = app(CreateQuoteAction::class);

        foreach ($leads->random(5) as $lead) {
            $createQuoteAction->handle(
                $lead,
                $this->getRandomPrice(),
                rand(7, 30)
            );
        }
    }

    /**
     * Get a random price based on package logic.
     */
    protected function getRandomPrice(): float
    {
        $prices = [850.00, 1150.00, 1950.00, 2500.00];
        return $prices[array_rand($prices)];
    }
}
