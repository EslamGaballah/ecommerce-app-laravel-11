<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dashboard\CategoriesController;
use App\Http\Controllers\Api\Dashboard\ProductsController;
use App\Http\Controllers\Api\Front\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Public catalog
Route::apiResource('categories', CategoriesController::class)->only(['index', 'show']);
Route::apiResource('products', ProductsController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', fn (Request $request) => $request->user());

    Route::apiResource('categories', CategoriesController::class)->only(['store', 'update', 'destroy']);

    Route::apiResource('products', ProductsController::class)->only(['store', 'update', 'destroy']);
    Route::post('products/{id}/restore', [ProductsController::class, 'restore']);
    Route::delete('products/{id}/force-delete', [ProductsController::class, 'forceDelete']);

    Route::delete('carts', [CartController::class, 'empty']);
    Route::apiResource('carts', CartController::class)->except(['show']);
});
