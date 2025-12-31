<?php

use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\Dashboard\RolesController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CheckUserType;
use Illuminate\Support\Facades\Route;

// Route::group([
    // 'middleware' => ['auth', 'auth.role:admin,seller'], // auth.type => CheckUserType alias
    // 'middleware' => ['auth'],
    // 'as' => 'dashboard.',
    // 'prefix' => 'dashboard'
// ], function () { 

Route::middleware(['auth','can:access-dashboard'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        
    Route::get('/',[DashboardController::class, 'index'])->name('home');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');


    Route::resource('categories', CategoriesController::class)->names('categories');

    Route::get('products/trash', [ProductsController::class, 'trash'])->name('products.trash');
    Route::put('products/{id}/restore', [ProductsController::class, 'restore'])->name('products.restore');
    Route::delete('products/{id}/force-delete', [ProductsController::class, 'forceDelete'])->name('products.forceDelete');
    Route::resource('products', ProductsController::class)->names('products');

    Route::resource('orders', OrdersController::class)->only(['index', 'show', 'update', 'destroy']);

    Route::resource('roles', RolesController::class)->names('roles');

    Route::resource('users', UserController::class)->names('users');
});