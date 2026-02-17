<?php

use App\Http\Controllers\Dashboard\AttributesController;
use App\Http\Controllers\Dashboard\AttributeValueController;
use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\GovernoratesController;
use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\Dashboard\RolesController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\TagsController;
use App\Http\Controllers\Dashboard\PostController;
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
        
    Route::get('/',[DashboardController::class, 'index'])->name('index');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');


    Route::resource('categories', CategoriesController::class)->names('categories');

    Route::resource('tags', TagsController::class)->names('tags');

    Route::prefix('products')->name('products.')->controller(ProductsController::class)->group(function () {
        Route::get('trash', 'trash')->name('trash');
        Route::put('{id}/restore', 'restore')->name('restore');
        Route::delete('{id}/force-delete', 'forceDelete')->name('forceDelete');
        Route::delete('variations/{variation}', 'deleteVariation')->name('variation.delete');
    });
    Route::resource('products', ProductsController::class);

    Route::resource('attributes', AttributesController::class);

    Route::resource('attribute_values', AttributeValueController::class);

    Route::resource('orders', OrdersController::class)->only(['index', 'show', 'update', 'destroy']);
    // Route::put('orders/{order}', [OrdersController::class, 'update'])
    // ->name('dashboard.orders.update');

    Route::resource('posts', PostController::class)->names('posts');


    Route::resource('roles', RolesController::class)->names('roles');

    Route::resource('users', UserController::class)->names('users');

    Route::resource('governorates', GovernoratesController::class);
    Route::patch('governorates/{governorate}/toggle-status',
        [GovernoratesController::class, 'toggleStatus'])
        ->name('governorates.toggle-status');
});