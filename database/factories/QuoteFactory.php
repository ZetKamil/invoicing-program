<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lead_id' => Lead::factory(),
            'quote_number' => 'Q-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'total_amount' => $this->faker->randomFloat(2, 500, 5000),
            'valid_until' => now()->addDays(14),
            'status' => $this->faker->randomElement(QuoteStatus::cases()),
        ];
    }
}
