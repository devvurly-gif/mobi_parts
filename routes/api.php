<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes (no middleware required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public product and category routes (no authentication required)
Route::get('/products/statistics', [\App\Http\Controllers\ProductController::class, 'statistics']);
Route::apiResource('products', \App\Http\Controllers\ProductController::class);
Route::get('/products/{product}/statistics', [\App\Http\Controllers\ProductController::class, 'statistics']);
Route::patch('/products/{product}/stock', [\App\Http\Controllers\ProductController::class, 'updateStock']);
// Update stock by code (EAN13) or ID (no creation; updates existing only)
Route::post('/products/stock-by-code', [\App\Http\Controllers\ProductController::class, 'updateStockByCode']);
// Soft delete routes
Route::post('/products/{id}/restore', [\App\Http\Controllers\ProductController::class, 'restore']);
Route::delete('/products/{id}/force-delete', [\App\Http\Controllers\ProductController::class, 'forceDelete']);

// Category routes
Route::get('/categories', function () {
    return \App\Models\Category::active()->get();
});
Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);

// Product images routes (public access)
Route::get('/product-images', [ProductImageController::class, 'index']);
Route::post('/product-images', [ProductImageController::class, 'store']);
Route::get('/product-images/{productImage}', [ProductImageController::class, 'show']);
Route::put('/product-images/{productImage}', [ProductImageController::class, 'update']);
Route::delete('/product-images/{productImage}', [ProductImageController::class, 'destroy']);
Route::post('/product-images/reorder', [ProductImageController::class, 'reorder']);

// First-time installation routes (public)
Route::get('/install/status', [InstallController::class, 'status']);
Route::post('/install', [InstallController::class, 'install']);
Route::post('/install/config', [InstallController::class, 'configure']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/revoke-all-tokens', [AuthController::class, 'revokeAllTokens']);
    Route::get('/token-info', [AuthController::class, 'tokenInfo']);
    
    // Profile routes
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // User management routes
    Route::apiResource('users', \App\Http\Controllers\UserController::class);

    // Test API route for Vue app
    Route::get('/test', function () {
        return response()->json([
            'message' => 'API is working!',
            'timestamp' => now(),
            'data' => [
                'framework' => 'Laravel',
                'frontend' => 'Vue 3',
                'state_management' => 'Pinia',
                'http_client' => 'Axios',
                'notifications' => 'Vue Toastification'
            ]
        ]);
    });
});