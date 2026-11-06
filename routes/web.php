<?php

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Http\Controllers\Front\OrdersController;
use App\Http\Controllers\Front\BundleController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\FavoritesController;
use App\Http\Controllers\Front\HomeController;
// use App\Http\Controllers\Front\OrdersController as FrontOrdersController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\PaymentController;
use App\Http\Controllers\Front\PostController;
use App\Http\Controllers\Front\ProductsController;
// use App\Http\Controllers\Front\RatingController;
use App\Http\Controllers\Front\ReviewController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ProfileController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// Route::get('/test', function (){

//     $order = Order::create([
//         'user_id' => 2,
//         'total'=> 100,
//         'status' => OrderStatus::Pending,
//     ]);

//     event(new OrderCreated($order));

// });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('lang/{lang}', function ($lang) {
//     if (in_array($lang, config('app.avilable_locales'))) {
//         session()->put('lang', $lang);
//     }
//     return back();
// })->name('lang.switch');
Route::get('lang/{lang}', [LangController::class, 'change'])->name('lang.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/bundles', [BundleController::class, 'index'])->name('bundles.index');
Route::get('/bundles/{bundle}', [BundleController::class, 'show'])->name('bundles.show');

Route::get('/about-us', [PageController::class, 'show'])->defaults('page', 'about')->name('pages.about');
Route::get('/faq', [PageController::class, 'show'])->defaults('page', 'faq')->name('pages.faq');
Route::get('/privacy-policy', [PageController::class, 'show'])->defaults('page', 'privacy')->name('pages.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'show'])->defaults('page', 'terms')->name('pages.terms');
Route::get('/shipping-policy', [PageController::class, 'show'])->defaults('page', 'shipping')->name('pages.shipping');
Route::get('/return-policy', [PageController::class, 'show'])->defaults('page', 'returns')->name('pages.returns');

Route::get('/contact-us', [ContactController::class, 'show'])->name('contact');
Route::post('/contact-us', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/front/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/front/product/{product:slug}', [ProductsController::class, 'show'])->name('products.show');
Route::post('/front/product/{product}/variation', [ProductsController::class, 'match'])
    ->name('variations.match');
Route::get('product/search', [ProductsController::class, 'search'])->name('products.search');

Route::delete('/cart', [CartController::class, 'empty'])->name('cart.empty');
Route::resource('cart', CartController::class)->only(['index', 'store', 'update', 'destroy']);
// Route::patch('/cart/{id}', [CartController::class, 'update']);
// Route::delete('/cart/{id}', [CartController::class, 'destroy']);
Route::get('/cart/json', [CartController::class, 'cartJson'])->name('cart.json');

Route::get('/posts',[PostController::class,'index']) ->name('front.posts.index');
Route::get('/posts/{post}',[PostController::class,'show']) ->name('front.posts.show');


Route::middleware('auth')->group(function() {
        Route::get('/checkout',[CheckoutController::class,'create'])->name('checkout');
        Route::post('/checkout',[CheckoutController::class,'store'])
                ->name('checkout.store');
        Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.applyCoupon');
        Route::get('/orders/{order}/pay', [PaymentController::class, 'show'])->name('front.payments.show');
        Route::post('/orders/{order}/pay', [PaymentController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('front.payments.store');
        Route::get('/my-orders', [OrdersController::class, 'index'])->name('front.orders.index');
        Route::get('/success/{order}', [OrdersController::class, 'success'])->name('front.orders.success');
        Route::get('/my-orders/{order}', [OrdersController::class, 'show'])->name('front.orders.show');

        Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites.index');
        // Route::post('/favorites/{product}', [FavoritesController::class, 'store'])->name('favorites.store');
        // Route::delete('/favorites/{product}', [FavoritesController::class, 'destroy'])->name('favorites.destroy');
        Route::post('/favorites/{product}', [FavoritesController::class, 'toggle'])
        ->name('favorites.toggle');

        Route::post('/products/{product}/review', [ReviewController::class, 'store'])
                // ->middleware('auth')
                ->name('products.review');

        Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}/open', [NotificationsController::class, 'open'])->name('notifications.open');
        Route::delete('/notifications/read', [NotificationsController::class, 'destroyRead'])->name('notifications.destroyRead');
        Route::delete('/notifications/{id}', [NotificationsController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/notifications/{id}/read', [NotificationsController::class, 'markAsRead'])
                ->name('notifications.markAsRead');
        Route::post('/notifications/read-all', [NotificationsController::class, 'markAllAsRead'])
                ->name('notifications.readAll');


        Route::post('/comments/{post}', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');



        // Route::post('/rate', [RatingController::class, 'store'])->name('rate.store');
});


require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';

// Inside the web middleware, so the 404 page has the visitor's language and session.
Route::fallback(fn () => abort(404));


