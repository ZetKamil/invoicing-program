<?php

namespace Database\Factories;

use App\Enums\TenantPlan;
use App\Models\Bedrijf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bedrijf>
 */
class BedrijfFactory extends Factory
{
    protected $model = Bedrijf::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'name'               => $name,
            'slug'               => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 99999),
            'plan'               => TenantPlan::START,
            'status'             => 'active',
            'vat_number'         => 'BE0' . $this->faker->numerify('#########'),
            'kbo_number'         => '0' . $this->faker->numerify('#########'),
            'peppol_endpoint_id' => $this->faker->numerify('0088:#########'),
            'settings'           => [],
        ];
    }
}
