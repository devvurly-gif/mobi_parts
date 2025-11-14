<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
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
            'parent_id' => $this->faker->optional(0.3)->passthrough(\App\Models\Brand::factory()), // 30% chance of having a parent
            'description' => $this->faker->sentence(10),
            'logo' => $this->faker->imageUrl(200, 200, 'business', true),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
        ];
    }
}
