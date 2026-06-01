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
        ];
    }
}
