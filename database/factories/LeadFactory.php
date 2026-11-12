<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement([LeadStatus::NEW, LeadStatus::AUDITED, LeadStatus::CONTACTED]),
            'metadata' => [
                'trailer_type' => fake()->randomElement(['Huifwagen', 'Koelwagen', 'Container', 'Dieplader', 'Silo', 'Kipper', 'Tankwagen']),
                'cargo_weight_kg' => fake()->numberBetween(1000, 24000),
                'pallet_count' => fake()->numberBetween(1, 33),
                'loading_date' => fake()->dateTimeBetween('now', '+14 days')->format('Y-m-d'),
                'delivery_date' => fake()->dateTimeBetween('+15 days', '+30 days')->format('Y-m-d'),
                'loading_address' => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->city() . ', ' . fake()->countryCode(),
                'delivery_address' => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->city() . ', ' . fake()->countryCode(),
                'message' => fake()->sentence(),
                'package' => fake()->randomElement(['start', 'pro', 'enterprise']),
            ],
        ];
    }
}
