<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::with(['parent', 'children']);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by root brands only
        if ($request->boolean('roots_only')) {
            $query->whereNull('parent_id');
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $brands = $query->get();

        // Return hierarchical structure if requested
        if ($request->boolean('hierarchical')) {
            $brands = $this->buildHierarchy($brands);
        }

        return response()->json($brands);
    }

    /**
     * Build hierarchical structure from flat brand list
     */
    private function buildHierarchy($brands)
    {
        $roots = $brands->whereNull('parent_id');
        
        $buildTree = function ($parent) use ($brands, &$buildTree) {
            $children = $brands->where('parent_id', $parent->id);
            $parent->children = $children->map(function ($child) use ($buildTree) {
                return $buildTree($child);
            });
            return $parent;
        };

        return $roots->map($buildTree);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Prevent circular reference (brand cannot be its own parent)
        if (isset($validated['parent_id']) && $validated['parent_id'] == $request->input('id')) {
            return response()->json([
                'message' => 'Brand cannot be its own parent'
            ], 422);
        }

        $brand = Brand::create($validated);
        $brand->load(['parent', 'children']);

        return response()->json([
            'message' => 'Brand created successfully',
            'brand' => $brand
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand): JsonResponse
    {
        $brand->load(['parent', 'children', 'products']);
        
        return response()->json([
            'brand' => $brand,
            'products_count' => $brand->products->count(),
            'active_products_count' => $brand->products->where('is_active', true)->count(),
            'children_count' => $brand->children->count(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id'])) {
            // Brand cannot be its own parent
            if ($validated['parent_id'] == $brand->id) {
                return response()->json([
                    'message' => 'Brand cannot be its own parent'
                ], 422);
            }

            // Prevent setting a descendant as parent
            $descendantIds = $this->getDescendantIds($brand);
            if (in_array($validated['parent_id'], $descendantIds)) {
                return response()->json([
                    'message' => 'Cannot set a descendant brand as parent'
                ], 422);
            }
        }

        $brand->update($validated);
        $brand->load(['parent', 'children']);

        return response()->json([
            'message' => 'Brand updated successfully',
            'brand' => $brand
        ]);
    }

    /**
     * Get all descendant IDs for a brand
     */
    private function getDescendantIds(Brand $brand): array
    {
        $ids = [];
        $children = $brand->children;
        
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        
        return $ids;
    }

    /**
     * Remove parent_id from brand (make it a root brand)
     */
    public function removeParent(Brand $brand): JsonResponse
    {
        $brand->update(['parent_id' => null]);
        $brand->load(['parent', 'children']);

        return response()->json([
            'message' => 'Brand restored to root level successfully',
            'brand' => $brand
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete brand with existing products'
            ], 422);
        }

        // Check if brand has children
        if ($brand->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete brand with child brands. Please delete or reassign child brands first.'
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'message' => 'Brand deleted successfully'
        ]);
    }
}
