<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Bedrijf;
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
            'bedrijf_id' => Bedrijf::factory(),
            'lead_id' => Lead::factory(),
            'quote_number' => 'Q-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'total_amount' => $this->faker->randomFloat(2, 500, 5000),
            'valid_until' => now()->addDays(14),
            'status' => $this->faker->randomElement(QuoteStatus::cases()),
            'trailer_type' => $this->faker->randomElement(['Huifwagen', 'Koelwagen', 'Container', 'Dieplader', 'Silo', 'Kipper', 'Tankwagen']),
            'loading_date' => $this->faker->dateTimeBetween('now', '+14 days')->format('Y-m-d'),
            'delivery_date' => $this->faker->dateTimeBetween('+15 days', '+30 days')->format('Y-m-d'),
            'cargo_weight_kg' => $this->faker->numberBetween(1000, 24000),
            'pallet_count' => $this->faker->numberBetween(1, 33),
            'loading_address' => $this->faker->streetAddress() . ', ' . $this->faker->postcode() . ' ' . $this->faker->city() . ', ' . $this->faker->countryCode(),
            'delivery_address' => $this->faker->streetAddress() . ', ' . $this->faker->postcode() . ' ' . $this->faker->city() . ', ' . $this->faker->countryCode(),
            'description' => $this->faker->sentence(),
        ];
    }
}
