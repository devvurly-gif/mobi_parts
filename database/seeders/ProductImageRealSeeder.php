<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageRealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the storage symlink exists (run: php artisan storage:link)
        // Expected folder for images: storage/app/public/products/smartphones/*.{jpg,png,jpeg}

        $disk = Storage::disk('public');
        $folder = 'products/smartphones';

        if (!$disk->exists($folder)) {
            $this->command?->warn("Folder public/$folder not found. Create it and add smartphone images.");
            return;
        }

        // Gather all images in the smartphones folder
        $files = collect($disk->files($folder))
            ->filter(function ($path) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                return in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
            })
            ->values();

        if ($files->isEmpty()) {
            $this->command?->warn("No images found in public/$folder. Add smartphone images and re-run.");
            return;
        }

        $this->command?->info('Found '. $files->count() . ' smartphone images. Updating product images...');

        // Option: clear existing product_images
        // DB::table('product_images')->truncate();

        $products = Product::query()->get();

        foreach ($products as $product) {
            // Decide how many images to assign per product (1-3)
            $count = rand(1, min(3, $files->count()));
            $chosen = $files->shuffle()->take($count)->values();

            // Remove existing images for a clean state
            ProductImage::where('product_id', $product->id)->delete();

            foreach ($chosen as $index => $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path, // stored relative path under public disk
                    'alt_text' => $product->name . ' - image ' . ($index + 1),
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $this->command?->info('Product images updated successfully.');
    }
}
