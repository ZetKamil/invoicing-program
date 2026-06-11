<?php

namespace Database\Factories;

use App\Models\Bedrijf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bedrijf>
 */
class BedrijfFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'vat_number' => 'BE0' . $this->faker->numerify('#########'),
            'peppol_id' => $this->faker->numerify('0088:#########'),
            'settings' => [],
        ];
    }
}
