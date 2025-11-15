<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        $unattached = $request->query('unattached', false);
        
        if ($unattached) {
            // Return all images without a product_id
            $images = ProductImage::whereNull('product_id')
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($images);
        }
        
        if ($productId) {
            $product = Product::findOrFail($productId);
            $images = $product->images()->get();
            return response()->json($images);
        }
        
        // Return all images if no filter specified
        $images = ProductImage::with('product')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($images);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alt_texts' => 'sometimes|array',
            'alt_texts.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $uploadedImages = [];
        $product = null;
        
        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
        }

        foreach ($request->file('images') as $index => $image) {
            // Store the image
            $path = $image->store('products', 'public');
            
            // Get alt text for this image
            $altText = $request->alt_texts[$index] ?? null;
            
            // Get the next sort order if product exists
            $sortOrder = 0;
            $isPrimary = false;
            
            if ($product) {
                $sortOrder = $product->images()->max('sort_order') ?? 0;
                $sortOrder += 1;
                $isPrimary = $product->images()->count() === 0; // First image is primary
            }
            
            // Create the image record
            $productImage = ProductImage::create([
                'product_id' => $product?->id,
                'image_path' => $path,
                'alt_text' => $altText,
                'sort_order' => $sortOrder,
                'is_primary' => $isPrimary,
            ]);

            $uploadedImages[] = $productImage;
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductImage $productImage): JsonResponse
    {
        return response()->json($productImage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductImage $productImage): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If setting as primary, unset other primary images for this product
        if ($request->has('is_primary') && $request->is_primary && $productImage->product_id) {
            ProductImage::where('product_id', $productImage->product_id)
                ->where('id', '!=', $productImage->id)
                ->update(['is_primary' => false]);
        }

        $productImage->update($request->only(['alt_text', 'sort_order', 'is_primary']));

        return response()->json([
            'message' => 'Image updated successfully',
            'image' => $productImage->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductImage $productImage): JsonResponse
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($productImage->image_path)) {
            Storage::disk('public')->delete($productImage->image_path);
        }

        // If this was the primary image, set another as primary
        if ($productImage->is_primary && $productImage->product_id) {
            $nextPrimary = ProductImage::where('product_id', $productImage->product_id)
                ->where('id', '!=', $productImage->id)
                ->first();
            
            if ($nextPrimary) {
                $nextPrimary->update(['is_primary' => true]);
            }
        }

        $productImage->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }

    /**
     * Reorder images for a product
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:product_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->image_ids as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $request->product_id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Images reordered successfully']);
    }

    /**
     * Attach images to a product
     */
    public function attachToProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'required|exists:product_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::findOrFail($request->product_id);
        $imageIds = $request->image_ids;
        
        // Get the current max sort order for this product
        $maxSortOrder = $product->images()->max('sort_order') ?? 0;
        $hasNoImages = $product->images()->count() === 0;
        
        // Update images to attach them to the product
        $updatedImages = [];
        foreach ($imageIds as $index => $imageId) {
            $image = ProductImage::findOrFail($imageId);
            
            // Only attach if not already attached to another product
            if ($image->product_id === null) {
                $image->update([
                    'product_id' => $product->id,
                    'sort_order' => $maxSortOrder + $index + 1,
                    'is_primary' => $hasNoImages && $index === 0, // First attached image becomes primary if product has no images
                ]);
                $updatedImages[] = $image->fresh();
            }
        }

        return response()->json([
            'message' => 'Images attached successfully',
            'images' => $updatedImages
        ]);
    }
}
