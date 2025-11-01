<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'primaryImage', 'images'])->get();

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'ean13' => 'nullable|string|unique:products,ean13',
            'prix_achat' => 'required|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);
        $product->load(['category', 'primaryImage']);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'primaryImage']);
        
        return response()->json([
            'product' => $product,
            'profit_margin' => $product->profit_margin,
            'profit_amount' => $product->profit_amount,
            'is_low_stock' => $product->isLowStock(),
            'is_out_of_stock' => $product->isOutOfStock(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'nullable|string',
            'ean13' => [
                'nullable',
                'string',
                Rule::unique('products', 'ean13')->ignore($product->id)
            ],
            'prix_achat' => 'sometimes|required|numeric|min:0',
            'prix_vente' => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'image' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);
        $product->load(['category', 'primaryImage']);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Restore a soft deleted product.
     */
    public function restore($id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return response()->json([
            'message' => 'Product restored successfully',
            'product' => $product->load(['category', 'primaryImage'])
        ]);
    }

    /**
     * Permanently delete a product.
     */
    public function forceDelete($id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();

        return response()->json([
            'message' => 'Product permanently deleted'
        ]);
    }

    /**
     * Get products statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::lowStock()->count(),
            'out_of_stock_products' => Product::where('stock_quantity', 0)->count(),
            'total_value' => Product::sum('prix_vente'),
            'average_profit_margin' => Product::selectRaw('AVG(((prix_vente - prix_achat) / prix_achat) * 100) as avg_margin')->value('avg_margin'),
        ];

        return response()->json($stats);
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Stock updated successfully',
            'product' => $product->fresh(['category', 'primaryImage'])
        ]);
    }

    /**
     * Update stock by product identifier (ID or EAN13)
     * This will NOT create products; only updates existing ones.
     */
    public function updateStockByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:products,id',
            'ean13' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        // Ensure at least one identifier is provided
        if (empty($validated['id']) && empty($validated['ean13'])) {
            return response()->json([
                'message' => 'Provide either product id or ean13'
            ], 422);
        }

        // Resolve product by ID first, then by EAN13
        $product = null;
        if (!empty($validated['id'])) {
            $product = Product::find($validated['id']);
        } elseif (!empty($validated['ean13'])) {
            // Pad EAN13 to 13 digits to handle leading zeros
            $ean13 = str_pad($validated['ean13'], 13, '0', STR_PAD_LEFT);
            $product = Product::where('ean13', $ean13)->first();
        }

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->update(['stock_quantity' => $validated['stock_quantity']]);

        return response()->json([
            'message' => 'Stock updated successfully',
            'product' => $product->fresh(['category', 'primaryImage'])
        ]);
    }
}
