<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageDownloadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure storage link exists
        // php artisan storage:link

        $disk = Storage::disk('public');
        $folder = 'products/smartphones';
        if (!$disk->exists($folder)) {
            $disk->makeDirectory($folder);
        }

        // Curated list of public placeholder images. These URLs are hotlink-friendly and require no API keys.
        // They are not brand-specific but look realistic enough for demos.
        $urls = [
            // Picsum seeds for consistent image variety
            'https://picsum.photos/seed/phone-a/800/800',
            'https://picsum.photos/seed/phone-b/800/800',
            'https://picsum.photos/seed/phone-c/800/800',
            'https://picsum.photos/seed/phone-d/800/800',
            'https://picsum.photos/seed/phone-e/800/800',
            'https://picsum.photos/seed/phone-f/800/800',
            'https://picsum.photos/seed/phone-g/800/800',
            'https://picsum.photos/seed/phone-h/800/800',
            'https://picsum.photos/seed/phone-i/800/800',
            'https://picsum.photos/seed/phone-j/800/800',
        ];

        $savedPaths = [];

        // Download each URL once into storage (skip if file already exists)
        foreach ($urls as $index => $url) {
            $filename = "smartphone-{$index}.jpg";
            $path = $folder . '/' . $filename;

            if (!$disk->exists($path)) {
                try {
                    $response = Http::timeout(20)->get($url);
                    if ($response->successful()) {
                        $disk->put($path, $response->body());
                        $this->command?->info("Downloaded: {$path}");
                    } else {
                        $this->command?->warn("Failed to download ({$response->status()}): {$url}");
                        continue;
                    }
                } catch (\Throwable $e) {
                    $this->command?->warn("Error downloading {$url}: " . $e->getMessage());
                    continue;
                }
            }

            $savedPaths[] = $path;
        }

        if (empty($savedPaths)) {
            $this->command?->warn('No images available to assign.');
            return;
        }

        $products = Product::query()->get();
        foreach ($products as $product) {
            // Reset existing images for a clean deterministic demo
            ProductImage::where('product_id', $product->id)->delete();

            // Assign 1-3 random images per product
            $count = rand(1, min(3, count($savedPaths)));
            $chosen = collect($savedPaths)->shuffle()->take($count)->values();

            foreach ($chosen as $i => $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'alt_text' => $product->name . ' - image ' . ($i + 1),
                    'sort_order' => $i,
                    'is_primary' => $i === 0,
                ]);
            }
        }

        $this->command?->info('Downloaded and assigned images to products.');
    }
}
