<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some predefined categories
        $categories = [
            [
                'name' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Clothing',
                'is_active' => true,
            ],
            [
                'name' => 'Books',
                'is_active' => true,
            ],
            [
                'name' => 'Home & Garden',
                'is_active' => true,
            ],
            [
                'name' => 'Sports',
                'is_active' => true,
            ],
            [
                'name' => 'Toys',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }

        // Create additional random categories using factory
        \App\Models\Category::factory(10)->create();
    }
}
