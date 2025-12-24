<?php

use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\Dashboard\RolesController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CheckUserType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('starter');
});

Route::group([
    // 'middleware' => ['auth', 'auth.role:admin,seller'], // auth.type => CheckUserType alias
    'middleware' => ['auth'],
    // 'as' => 'dashboard.',
    // 'prefix' => 'dashboard'
], function () { 

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Route::get('/',[DashboardController::class, 'index'])->name('dashboard');

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

    Route::resource('roles', RolesController::class)
        ->names('dashboard.roles');

        Route::resource('users', UserController::class)
        ->names('dashboard.users');
});