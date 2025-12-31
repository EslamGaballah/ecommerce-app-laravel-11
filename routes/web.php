<?php

use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\ProductsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/front/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/front/product/{id}', [ProductsController::class, 'show'])->name('front.products.show');
// Route::get('/front/product/{product:slug}', [ProductsController::class, 'show'])->name('products.show');
Route::get('/front/product/{slug}', [ProductsController::class, 'show'])->name('products.show');

Route::resource('cart', CartController::class);
// ->names('cart');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');

Route::middleware('auth')->group(function() {
        Route::get('/checkout',[CheckoutController::class,'create'])->name('checkout');
        Route::post('/checkout',[CheckoutController::class,'store'])
                ->name('checkout.store');
        // Route::get('/my-orders', [OrdersController::class, 'index'])->name('orders.index');
});


require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';


