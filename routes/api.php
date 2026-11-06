<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dashboard\CategoriesController;
use App\Http\Controllers\Api\Dashboard\ProductsController;
use App\Http\Controllers\Api\Front\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// The prefix keeps these names from replacing the storefront's products.index and products.show.
Route::name('api.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');

    // Public catalog
    Route::apiResource('categories', CategoriesController::class)->only(['index', 'show']);
    Route::apiResource('products', ProductsController::class)->only(['index', 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', fn (Request $request) => $request->user())->name('user');

        Route::apiResource('categories', CategoriesController::class)->only(['store', 'update', 'destroy']);

        Route::apiResource('products', ProductsController::class)->only(['store', 'update', 'destroy']);
        Route::post('products/{id}/restore', [ProductsController::class, 'restore'])->name('products.restore');
        Route::delete('products/{id}/force-delete', [ProductsController::class, 'forceDelete'])->name('products.force-delete');

        Route::delete('carts', [CartController::class, 'empty'])->name('carts.empty');
        Route::apiResource('carts', CartController::class)->except(['show']);
    });
});
