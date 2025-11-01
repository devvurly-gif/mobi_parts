<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateDefaultProductImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:generate-default-image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a default placeholder image for products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $width = 800;
        $height = 800;
        
        // Create image with transparent background
        $image = imagecreatetruecolor($width, $height);
        
        // Enable alpha blending
        imagealphablending($image, true);
        imagesavealpha($image, true);
        
        // Define colors
        $background = imagecolorallocatealpha($image, 245, 247, 250, 0); // Light gray-blue background
        $boxColor = imagecolorallocate($image, 229, 231, 235); // Light gray box
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 80); // Dark shadow with transparency
        $accentColor = imagecolorallocate($image, 99, 102, 241); // Indigo accent
        $textColor = imagecolorallocate($image, 156, 163, 175); // Gray text
        
        // Fill background
        imagefill($image, 0, 0, $background);
        
        // Draw box shadow (offset slightly)
        $boxX = ($width - 500) / 2 + 8;
        $boxY = ($height - 500) / 2 + 8;
        imagefilledrectangle($image, $boxX, $boxY, $boxX + 500, $boxY + 500, $shadowColor);
        
        // Draw main product box
        $boxX = ($width - 500) / 2;
        $boxY = ($height - 500) / 2;
        imagefilledrectangle($image, $boxX, $boxY, $boxX + 500, $boxY + 500, $boxColor);
        
        // Draw border
        imagerectangle($image, $boxX, $boxY, $boxX + 500, $boxY + 500, $accentColor);
        
        // Draw gradient effect (simulated with multiple rectangles)
        $gradientSteps = 20;
        for ($i = 0; $i < $gradientSteps; $i++) {
            $alpha = (int)(127 * ($i / $gradientSteps));
            $gradientColor = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
            $y = $boxY + ($i * ($height - $boxY * 2) / $gradientSteps);
            imagefilledrectangle($image, $boxX, $y, $boxX + 500, $y + 25, $gradientColor);
        }
        
        // Draw product box representation (centered)
        $productX = $boxX + 100;
        $productY = $boxY + 100;
        $productW = 300;
        $productH = 200;
        
        // Product box shadow
        $shadowAlpha = imagecolorallocatealpha($image, 0, 0, 0, 100);
        imagefilledrectangle($image, $productX + 5, $productY + 5, $productX + $productW + 5, $productY + $productH + 5, $shadowAlpha);
        
        // Main product box
        imagefilledrectangle($image, $productX, $productY, $productX + $productW, $productY + $productH, imagecolorallocate($image, 255, 255, 255));
        imagerectangle($image, $productX, $productY, $productX + $productW, $productY + $productH, $accentColor);
        
        // Draw product icon (simplified box with rounded corners effect)
        $iconX = $productX + 75;
        $iconY = $productY + 40;
        $iconW = 150;
        $iconH = 120;
        imagefilledrectangle($image, $iconX, $iconY, $iconX + $iconW, $iconY + $iconH, $boxColor);
        imagerectangle($image, $iconX, $iconY, $iconX + $iconW, $iconY + $iconH, $accentColor);
        
        // Draw screen/display representation
        $screenX = $iconX + 10;
        $screenY = $iconY + 10;
        $screenW = $iconW - 20;
        $screenH = $iconH - 20;
        imagefilledrectangle($image, $screenX, $screenY, $screenX + $screenW, $screenY + $screenH, imagecolorallocate($image, 243, 244, 246));
        imagerectangle($image, $screenX, $screenY, $screenX + $screenW, $screenY + $screenH, $textColor);
        
        // Draw some detail lines
        imageline($image, $screenX + 15, $screenY + 20, $screenX + $screenW - 15, $screenY + 20, $textColor);
        imageline($image, $screenX + 15, $screenY + 40, $screenX + $screenW - 15, $screenY + 40, $textColor);
        imageline($image, $screenX + 15, $screenY + 60, $screenX + $screenW - 30, $screenY + 60, $textColor);
        
        // Draw decorative elements at bottom
        imagefilledellipse($image, $boxX + 150, $boxY + 420, 50, 50, $accentColor);
        imagefilledellipse($image, $boxX + 250, $boxY + 440, 30, 30, $textColor);
        imagefilledellipse($image, $boxX + 350, $boxY + 420, 50, 50, $accentColor);
        
        // Save to storage
        $disk = Storage::disk('public');
        $path = 'products/default-placeholder.png';
        
        // Create directory if it doesn't exist
        $disk->makeDirectory('products');
        
        // Output image to buffer
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Save to storage
        $disk->put($path, $imageData);
        
        // Free memory
        imagedestroy($image);
        
        $this->info("Default product image created at: storage/app/public/{$path}");
        $this->info("URL: " . asset('storage/' . $path));
        
        return Command::SUCCESS;
    }
}

