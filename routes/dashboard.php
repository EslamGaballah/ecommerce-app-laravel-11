<?php

use App\Http\Controllers\Dashboard\AttributesController;
use App\Http\Controllers\Dashboard\AttributeValueController;
use App\Http\Controllers\Dashboard\BrandsController;
use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\CouponController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\GovernoratesController;
use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Dashboard\PostController;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\Dashboard\RolesController;
use App\Http\Controllers\Dashboard\TagsController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:access-dashboard'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // Profile
        Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
        });

        // Categories, products, and orders authorize through their policies.
        Route::resource('categories', CategoriesController::class);

        Route::prefix('products')->name('products.')->controller(ProductsController::class)->group(function () {
            Route::get('trash', 'trash')->name('trash');
            Route::put('{id}/restore', 'restore')->name('restore');
            Route::delete('{id}/force-delete', 'forceDelete')->name('forceDelete');
            Route::delete('variations/{variation}', 'deleteVariation')->name('variation.delete');
        });
        Route::resource('products', ProductsController::class);

        Route::resource('orders', OrdersController::class)->only(['index', 'show', 'update', 'destroy']);

        // The remaining sections each have one permission.
        Route::middleware('can:manage-brands')->group(function () {
            Route::resource('brands', BrandsController::class);
        });

        Route::middleware('can:manage-tags')->group(function () {
            Route::resource('tags', TagsController::class);
        });

        Route::middleware('can:manage-attributes')->group(function () {
            Route::resource('attributes', AttributesController::class)->except(['show']);
            Route::resource('attribute-values', AttributeValueController::class)->except(['show']);
        });

        Route::middleware('can:manage-coupons')->group(function () {
            Route::resource('coupons', CouponController::class)->except(['show']);
        });

        Route::middleware('can:manage-posts')->group(function () {
            Route::resource('posts', PostController::class);
        });

        Route::middleware('can:manage-roles')->group(function () {
            Route::resource('roles', RolesController::class);
        });

        Route::middleware('can:manage-users')->group(function () {
            Route::resource('users', UserController::class);
        });

        Route::middleware('can:manage-governorates')->group(function () {
            Route::resource('governorates', GovernoratesController::class)->except(['show']);
            Route::patch('governorates/{governorate}/toggle-status', [GovernoratesController::class, 'toggleStatus'])
                ->name('governorates.toggle-status');
        });
    });
