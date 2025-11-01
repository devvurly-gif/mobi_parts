<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'ean13',
        'prix_achat',
        'prix_vente',
        'stock_quantity',
        'min_stock',
        'image',
        'is_active'
    ];

    protected $casts = [
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
        'min_stock' => 'integer',
    ];

    protected $appends = [
        'primary_image',
    ];


    // Relationship with category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship with images
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    // Get primary image relationship
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->latest();
    }

    // Accessor for primary_image that ensures it's always available
    public function getPrimaryImageAttribute()
    {
        // Try to get from eager-loaded primaryImage relationship first
        if ($this->relationLoaded('primaryImage')) {
            $primary = $this->getRelation('primaryImage');
            if ($primary) {
                return $primary;
            }
        }
        
        // If no primary image from relationship, try to get from loaded images collection
        if ($this->relationLoaded('images')) {
            $images = $this->getRelation('images');
            if ($images && $images->isNotEmpty()) {
                // First try to find one marked as primary
                $primary = $images->where('is_primary', true)->first();
                if ($primary) {
                    return $primary;
                }
                // Fallback to first image if no primary is set
                return $images->first();
            }
        }
        
        // If relationships are not loaded, try to get from database (one-time query)
        // This handles cases where relationships weren't eager loaded
        $images = $this->images;
        if ($images && $images->isNotEmpty()) {
            $primary = $images->where('is_primary', true)->first();
            if ($primary) {
                return $primary;
            }
            return $images->first();
        }
        
        // Return default placeholder image when no images exist
        return $this->getDefaultImage();
    }

    /**
     * Override toArray to ensure primary_image is always included and never null
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Always ensure primary_image is set and never null
        // The accessor handles finding images or returning default
        $primaryImage = $this->getPrimaryImageAttribute();
        
        // Convert to array if it's an object (for ProductImage models)
        if (is_object($primaryImage) && method_exists($primaryImage, 'toArray')) {
            $array['primary_image'] = $primaryImage->toArray();
        } elseif (is_object($primaryImage)) {
            // For stdClass objects, convert to array
            $array['primary_image'] = (array) $primaryImage;
        } else {
            // Already an array
            $array['primary_image'] = $primaryImage;
        }
        
        return $array;
    }

    /**
     * Get a default placeholder image object
     * Returns a ProductImage-like object with placeholder URL
     */
    protected function getDefaultImage()
    {
        // Check if default placeholder exists in storage
        $defaultImagePath = 'products/default-placeholder.png';
        $baseUrl = config('filesystems.disks.public.url', asset('storage'));
        
        // Ensure base URL ends with /storage
        if (!str_ends_with($baseUrl, '/storage')) {
            $baseUrl = rtrim($baseUrl, '/') . '/storage';
        }
        
        // Build URL for default placeholder
        $imageUrl = rtrim($baseUrl, '/') . '/' . ltrim($defaultImagePath, '/');
        
        // Check if file exists, if not generate it
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($defaultImagePath)) {
            // Try to generate the default image
            try {
                \Illuminate\Support\Facades\Artisan::call('product:generate-default-image');
            } catch (\Exception $e) {
                // If generation fails, use placeholder service as fallback
                $imageUrl = 'https://via.placeholder.com/800x800/E5E7EB/9CA3AF?text=' . urlencode(substr($this->name, 0, 20));
            }
        }
        
        // Return as array for proper JSON serialization
        return [
            'id' => null,
            'product_id' => $this->id,
            'alt_text' => $this->name . ' - No image available',
            'sort_order' => 0,
            'is_primary' => true,
            'image_url' => $imageUrl,
        ];
    }

    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for low stock products
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= min_stock');
    }

    // Scope for products by category
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessor for profit margin
    public function getProfitMarginAttribute()
    {
        if ($this->prix_achat > 0) {
            return (($this->prix_vente - $this->prix_achat) / $this->prix_achat) * 100;
        }
        return 0;
    }

    // Accessor for profit amount
    public function getProfitAmountAttribute()
    {
        return $this->prix_vente - $this->prix_achat;
    }

    // Check if product is low on stock
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    // Check if product is out of stock
    public function isOutOfStock()
    {
        return $this->stock_quantity <= 0;
    }
}
