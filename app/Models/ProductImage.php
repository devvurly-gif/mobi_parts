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
        
        // Use Storage::url() which automatically handles APP_URL and storage symlink
        // This is the Laravel-recommended way to generate storage URLs
        try {
            $url = Storage::disk('public')->url($this->image_path);
            
            // Ensure we have a full URL (not relative)
            if (str_starts_with($url, 'http')) {
                return $url;
            }
            
            // If Storage::url() returns a relative path, prepend APP_URL
            $appUrl = config('app.url', env('APP_URL', 'http://localhost'));
            $appUrl = rtrim($appUrl, '/');
            
            // If URL starts with /storage, use it directly
            if (str_starts_with($url, '/storage')) {
                return $appUrl . $url;
            }
            
            // Otherwise, ensure /storage prefix
            return $appUrl . '/storage/' . ltrim($url, '/');
            
        } catch (\Exception $e) {
            // Fallback: manual URL construction
            $appUrl = config('app.url', env('APP_URL', 'http://localhost'));
            $appUrl = rtrim($appUrl, '/');
            
            return $appUrl . '/storage/' . ltrim($this->image_path, '/');
        }
    }
}
