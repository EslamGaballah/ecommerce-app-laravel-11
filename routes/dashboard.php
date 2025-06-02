<?php

use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('starter');
});

Route::resource('categories', CategoriesController::class)
->names('dashboard.categories');

Route::get('products/trash', [ProductsController::class, 'trash'])
->name('dashboard.products.trash');
Route::put('products/{id}/restore', [ProductsController::class, 'restore'])
->name('dashboard.products.restore');
Route::delete('products/{id}/force-delete', [ProductsController::class, 'forceDelete'])
->name('dashboard.products.forceDelete');
Route::resource('products', ProductsController::class)
->names('dashboard.products');