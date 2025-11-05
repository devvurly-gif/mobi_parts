<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected $appends = [
        'image_url',
    ];

    protected $hidden = [
        'image_path', // Hide from JSON, use image_url instead
    ];

    // Relationship with product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scope for primary images
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Scope for ordered images
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // Accessor for full image URL
    public function getImageUrlAttribute()
    {
        // If already a full URL, return as is
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        
        // If image_path is empty, return null
        if (empty($this->image_path)) {
            return null;
        }
        
        // Get APP_URL from config
        $appUrl = config('app.url', env('APP_URL', 'http://localhost'));
        $appUrl = rtrim($appUrl, '/');
        
        // Clean the image path (remove leading slash if present)
        $imagePath = ltrim($this->image_path, '/');
        
        // Build the full URL: APP_URL + /storage + /products/filename.jpg
        // image_path format: 'products/filename.jpg' (from store('products', 'public'))
        return $appUrl . '/storage/' . $imagePath;
    }
}
