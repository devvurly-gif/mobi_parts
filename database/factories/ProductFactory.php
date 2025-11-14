<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prixAchat = $this->faker->randomFloat(2, 10, 500);
        $prixVente = $prixAchat + $this->faker->randomFloat(2, 5, 100); // Sale price is higher than purchase price
        
        return [
            'name' => $this->faker->words(3, true),
            'category_id' => \App\Models\Category::factory(),
            'brand_id' => $this->faker->optional(0.7)->passthrough(\App\Models\Brand::factory()), // 70% chance of having a brand
            'description' => $this->faker->paragraph(3),
            'ean13' => $this->faker->ean13(),
            'prix_achat' => $prixAchat,
            'prix_vente' => $prixVente,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'min_stock' => $this->faker->numberBetween(5, 20),
            'image' => '/products/default-placeholder.png',
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
        ];
    }
}
