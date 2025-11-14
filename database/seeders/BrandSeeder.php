<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some predefined brands
        $brands = [
            [
                'name' => 'Samsung',
                'description' => 'South Korean multinational electronics company',
                'logo' => 'https://logos-world.net/wp-content/uploads/2020/06/Samsung-Logo.png',
                'is_active' => true,
            ],
            [
                'name' => 'Apple',
                'description' => 'American multinational technology company',
                'logo' => 'https://logos-world.net/wp-content/uploads/2020/04/Apple-Logo.png',
                'is_active' => true,
            ],
            [
                'name' => 'Sony',
                'description' => 'Japanese multinational conglomerate',
                'logo' => 'https://logos-world.net/wp-content/uploads/2020/06/Sony-Logo.png',
                'is_active' => true,
            ],
            [
                'name' => 'LG',
                'description' => 'South Korean multinational electronics company',
                'logo' => 'https://logos-world.net/wp-content/uploads/2020/06/LG-Logo.png',
                'is_active' => true,
            ],
            [
                'name' => 'Nike',
                'description' => 'American multinational corporation',
                'logo' => 'https://logos-world.net/wp-content/uploads/2020/04/Nike-Logo.png',
                'is_active' => true,
            ],
            [
                'name' => 'Adidas',
                'description' => 'German multinational corporation',
                'logo' => 'https://logos-world.net/wp-content/uploads/2020/04/Adidas-Logo.png',
                'is_active' => true,
            ],
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::create($brand);
        }

        // Create additional random brands using factory
        \App\Models\Brand::factory(15)->create();
    }
}

