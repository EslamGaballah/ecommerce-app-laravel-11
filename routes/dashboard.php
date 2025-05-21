<?php

use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('starter');
});

Route::resource('categories', CategoriesController::class)->names('dashboard.categories');

Route::resource('products', ProductsController::class)->names('dashboard.products');