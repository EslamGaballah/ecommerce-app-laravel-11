<?php

use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\ProductsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('starter');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('/product/{product:slug}', [ProductsController::class, 'show'])->name('products.show');

Route::get('/front/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/front/product/{id}', [ProductsController::class, 'show'])->name('front.products.show');

Route::resource('cart', CartController::class)->names('cart');
// Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');

Route::get('/checkout/create',[CheckoutController::class,'create'])->name('checkout.create');
Route::post('/checkout',[CheckoutController::class,'store'])
        ->name('checkout.store');

require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
