<?php

use App\Http\Controllers\Api\Dashboard\CategoriesController;
use App\Http\Controllers\Api\Dashboard\ProductsController;
use App\Http\Controllers\Api\Front\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('categories',CategoriesController::class);
// Route::post('categories/store',CategoriesController::class)
//         ->name('store');

Route::apiResource('products',ProductsController::class);
Route::post('products/{id}/restore', [ProductsController::class, 'restore']);
Route::delete('products/{id}/force-delete', [ProductsController::class, 'forceDelete']);

Route::apiResource('carts',CartController::class);

