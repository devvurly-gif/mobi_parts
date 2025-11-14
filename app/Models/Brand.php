<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'description',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship with parent brand
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'parent_id');
    }

    // Relationship with child brands
    public function children(): HasMany
    {
        return $this->hasMany(Brand::class, 'parent_id')->orderBy('name');
    }

    // Relationship with products
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Get all descendants (children, grandchildren, etc.)
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    // Get all ancestors (parent, grandparent, etc.)
    public function ancestors()
    {
        $ancestors = collect();
        $brand = $this->parent;
        
        while ($brand) {
            $ancestors->push($brand);
            $brand = $brand->parent;
        }
        
        return $ancestors->reverse();
    }

    // Check if brand is a root brand (no parent)
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    // Get depth level (0 for root, 1 for first level, etc.)
    public function getDepthAttribute(): int
    {
        return $this->ancestors()->count();
    }

    // Scope for active brands
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

