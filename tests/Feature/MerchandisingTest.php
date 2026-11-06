<?php

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Governorate;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Database\Seeders\GovernorateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(GovernorateSeeder::class);
});

function merchUser(string $role = 'user'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', $role)->firstOrFail()->id);

    return $user;
}

function merchStaff(array $permissions): User
{
    $role = Role::create(['name' => 'staff-'.uniqid()]);
    $role->permissions()->sync(Permission::whereIn('name', ['access-dashboard', ...$permissions])->pluck('id'));
    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    return $user;
}

function makeBundle($products, array $tiers, array $attributes = []): Bundle
{
    $bundle = Bundle::create($attributes + [
        'name_ar' => 'باندل', 'name_en' => 'Bundle', 'slug' => 'bundle-'.uniqid(), 'is_active' => true,
    ]);

    $bundle->products()->sync(collect($products)->pluck('id'));

    foreach ($tiers as [$quantity, $type, $value]) {
        $bundle->tiers()->create(['min_quantity' => $quantity, 'discount_type' => $type, 'discount_value' => $value]);
    }

    return $bundle;
}

function addToCart($test, User $user, Product $product, int $quantity = 1): void
{
    $test->actingAs($user)->postJson('/cart', ['product_id' => $product->id, 'quantity' => $quantity])->assertCreated();
}

function checkoutData(): array
{
    return [
        'first_name' => 'a', 'last_name' => 'b', 'email' => 'a@b.test', 'phone_number' => '1', 'city' => 'x',
        'street_address' => 'y', 'country' => 'EG', 'governorate_id' => Governorate::first()->id, 'payment_method' => 'cash',
    ];
}

// ---------- Bundles ----------

test('choosing the set number of products gives the bundle discount on the order', function () {
    $products = Product::factory()->count(3)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[3, 'percent', 20]]);
    $user = merchUser();

    foreach ($products as $product) {
        addToCart($this, $user, $product);
    }

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();

    $order = Order::first();
    $shipping = (float) Governorate::first()->shipping_price;

    expect((float) $order->bundle_discount)->toBe(60.0)
        ->and((float) $order->discount)->toBe(60.0)
        ->and((float) $order->total)->toBe(240.0 + $shipping + (float) Setting::get('tax'));
});

test('no discount below the first level', function () {
    $products = Product::factory()->count(3)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[3, 'percent', 20]]);
    $user = merchUser();

    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();

    expect((float) Order::first()->bundle_discount)->toBe(0.0);
});

test('the biggest level reached applies, and only to products in the bundle', function () {
    $products = Product::factory()->count(4)->create(['price' => 100, 'stock' => 10]);
    $other = Product::factory()->create(['price' => 1000, 'stock' => 10]);
    makeBundle($products, [[2, 'percent', 10], [4, 'percent', 25]]);
    $user = merchUser();

    addToCart($this, $user, $other);
    foreach ($products as $product) {
        addToCart($this, $user, $product);
    }

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();

    expect((float) Order::first()->bundle_discount)->toBe(100.0);
});

test('a fixed discount applies once and never exceeds the price', function () {
    $products = Product::factory()->count(2)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[2, 'fixed', 50]]);
    $user = merchUser();
    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();
    expect((float) Order::first()->bundle_discount)->toBe(50.0);

    Order::query()->delete();
    Bundle::query()->delete();
    makeBundle($products, [[2, 'fixed', 9999]]);
    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();
    expect((float) Order::first()->bundle_discount)->toBe(200.0);
});

test('quantity of one product counts unless the bundle asks for different products', function () {
    $products = Product::factory()->count(3)->create(['price' => 100, 'stock' => 10]);
    $user = merchUser();
    addToCart($this, $user, $products[0], 3);

    $counts = makeBundle($products, [[3, 'percent', 10]]);
    $service = app(App\Services\Bundle\BundleService::class);
    $items = app(App\Services\Order\CartService::class)->items();

    expect($service->calculate($items)['discount'])->toBe(30.0);

    $counts->update(['distinct_products' => true]);
    Illuminate\Support\Once::flush();

    expect($service->calculate($items)['discount'])->toBe(0.0);
});

test('a bundle that is off, not started, or finished gives nothing', function (array $attributes) {
    $products = Product::factory()->count(2)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[2, 'percent', 50]], $attributes);
    $user = merchUser();
    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();

    expect((float) Order::first()->bundle_discount)->toBe(0.0);
})->with([
    'off' => [['is_active' => false]],
    'not started' => [['starts_at' => now()->addDay()]],
    'finished' => [['ends_at' => now()->subDay()]],
]);

test('a product counts toward one bundle only, the one giving the most', function () {
    $products = Product::factory()->count(2)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[2, 'percent', 10]]);
    makeBundle($products, [[2, 'percent', 30]]);
    $user = merchUser();
    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();

    expect((float) Order::first()->bundle_discount)->toBe(60.0);
});

test('a coupon applies to what is left after the bundle discount', function () {
    $products = Product::factory()->count(2)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[2, 'percent', 50]]);
    Coupon::forceCreate(['code' => 'TEN', 'type' => 'percent', 'value' => 10, 'is_active' => true]);
    $user = merchUser();
    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $response = $this->actingAs($user)->postJson(route('checkout.applyCoupon'), ['code' => 'TEN'])->assertOk();

    expect((float) $response->json('totals.discount'))->toBe(110.0)
        ->and((float) $response->json('totals.bundle_discount'))->toBe(100.0);
});

test('the cart page shows the discount and what to add next, and its numbers update', function () {
    $products = Product::factory()->count(3)->create(['price' => 100, 'stock' => 10]);
    $bundle = makeBundle($products, [[2, 'percent', 10], [3, 'percent', 20]]);
    $user = merchUser();

    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->get('/cart')->assertOk()
        ->assertSee($bundle->name)
        ->assertSee(__('app.bundle_add_more', ['count' => 1, 'discount' => '20%']))
        ->assertSee(__('app.bundle_discount'));

    $item = App\Models\Cart::first();

    $this->actingAs($user)->patchJson("/cart/{$item->id}", ['quantity' => 2])->assertOk()
        ->assertJsonPath('bundle_discount', App\Helpers\Currency::format(60))
        ->assertJsonPath('cart_total', App\Helpers\Currency::format(240));
});

test('the bundle pages show running bundles only', function () {
    $products = Product::factory()->count(2)->create(['stock' => 5]);
    $running = makeBundle($products, [[2, 'percent', 10]], ['name_en' => 'Running Set', 'name_ar' => 'Running Set']);
    $finished = makeBundle($products, [[2, 'percent', 10]], ['name_en' => 'Old Set', 'name_ar' => 'Old Set', 'ends_at' => now()->subDay()]);

    $this->get('/bundles')->assertOk()->assertSee('Running Set')->assertDontSee('Old Set');
    $this->get('/')->assertOk()->assertSee('Running Set');
    $this->get(route('bundles.show', $running))->assertOk()->assertSee($products[0]->name);
    $this->get(route('bundles.show', $finished))->assertNotFound();
    $this->get(route('products.show', $products[0]->slug))->assertOk()->assertSee('Running Set');
    $this->get(route('bundles.show', $running), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertDontSee('<html', false);
});

test('staff manage bundles with the permission only, and bad discounts are rejected', function () {
    $products = Product::factory()->count(2)->create();
    $payload = [
        'name_ar' => 'عرض', 'name_en' => 'My Offer', 'is_active' => '1',
        'products' => $products->pluck('id')->all(),
        'tiers' => [['min_quantity' => 3, 'discount_type' => 'percent', 'discount_value' => 15], ['min_quantity' => 2, 'discount_type' => 'fixed', 'discount_value' => 50]],
    ];

    $this->actingAs(merchStaff([]))->get('/dashboard/bundles')->assertForbidden();
    $this->actingAs(merchStaff([]))->post('/dashboard/bundles', $payload)->assertForbidden();

    $staff = merchStaff(['manage-bundles']);

    $this->actingAs($staff)->get('/dashboard/bundles/create')->assertOk();
    $this->actingAs($staff)->post('/dashboard/bundles', $payload)->assertRedirect('/dashboard/bundles')->assertSessionHasNoErrors();
    $this->actingAs($staff)->post('/dashboard/bundles', $payload)->assertRedirect('/dashboard/bundles');

    $bundle = Bundle::with('tiers')->first();

    expect(Bundle::pluck('slug')->all())->toEqualCanonicalizing(['my-offer', 'my-offer-2'])
        ->and($bundle->tiers->pluck('min_quantity')->all())->toBe([2, 3])
        ->and($bundle->products()->count())->toBe(2);

    $this->actingAs($staff)->get(route('dashboard.bundles.edit', $bundle))->assertOk();

    $this->actingAs($staff)->put(route('dashboard.bundles.update', $bundle), ['tiers' => [['min_quantity' => 2, 'discount_type' => 'percent', 'discount_value' => 150]]] + $payload)
        ->assertSessionHasErrors('tiers.0.discount_value');
    $this->actingAs($staff)->put(route('dashboard.bundles.update', $bundle), ['tiers' => [['min_quantity' => 2, 'discount_type' => 'percent', 'discount_value' => 5], ['min_quantity' => 2, 'discount_type' => 'percent', 'discount_value' => 6]]] + $payload)
        ->assertSessionHasErrors('tiers');
    $this->actingAs($staff)->put(route('dashboard.bundles.update', $bundle), ['products' => [$products[0]->id]] + $payload)
        ->assertSessionHasErrors('products');

    $this->actingAs($staff)->put(route('dashboard.bundles.update', $bundle), ['tiers' => [['min_quantity' => 4, 'discount_type' => 'fixed', 'discount_value' => 9]]] + $payload)
        ->assertSessionHasNoErrors();
    expect($bundle->fresh()->tiers)->toHaveCount(1);

    $this->actingAs($staff)->delete(route('dashboard.bundles.destroy', $bundle))->assertRedirect();
    expect(Bundle::count())->toBe(1);
});

test('the sample bundle seeder runs twice without repeating', function () {
    $this->seed(Database\Seeders\BundleSeeder::class);
    $this->seed(Database\Seeders\BundleSeeder::class);

    expect(Bundle::count())->toBe(2);
});

// ---------- Discovery ----------

test('a view counts once per visitor session', function () {
    $product = Product::factory()->create();

    $this->get(route('products.show', $product->slug))->assertOk();
    $this->get(route('products.show', $product->slug))->assertOk();

    expect($product->fresh()->views_count)->toBe(1);

    $this->flushSession();
    $this->get(route('products.show', $product->slug))->assertOk();

    expect($product->fresh()->views_count)->toBe(2);
});

test('counting a view does not change when the product was updated', function () {
    $product = Product::factory()->create();
    Product::whereKey($product->id)->update(['updated_at' => now()->subDays(5)]);
    $before = $product->fresh()->updated_at;

    $this->get(route('products.show', $product->slug))->assertOk();

    expect($product->fresh()->updated_at->equalTo($before))->toBeTrue();
});

test('best sellers count real orders and leave out canceled ones', function () {
    [$top, $canceled, $none] = Product::factory()->count(3)->create();
    $user = merchUser();

    $sell = function (Product $product, int $quantity, string $status) use ($user) {
        $order = Order::create(['user_id' => $user->id, 'status' => $status, 'payment_status' => 'pending', 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => 1]);
        $order->items()->forceCreate(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'x', 'price' => 1, 'quantity' => $quantity]);
    };

    $sell($top, 5, 'completed');
    $sell($canceled, 50, 'canceled');

    expect(Product::bestSelling()->pluck('id')->all())->toBe([$top->id]);

    $this->get('/')->assertOk()->assertSee(__('app.best_sellers'));
    $this->get(route('products.index', ['sort_by' => 'best_selling']))->assertOk();
});

test('home sections appear only when they have products', function () {
    Product::factory()->create(['stock' => 5]);

    $this->get('/')->assertOk()
        ->assertDontSee(__('app.featured_products_text'))
        ->assertDontSee(__('app.most_viewed_text'))
        ->assertDontSee(__('app.best_sellers_text'));

    $picked = Product::factory()->create(['stock' => 5, 'is_featured' => true, 'views_count' => 3]);

    $this->get('/')->assertOk()
        ->assertSee(__('app.featured_products_text'))
        ->assertSee(__('app.most_viewed_text'));

    $this->get(route('products.index', ['featured' => 1]))->assertOk()
        ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$picked->id]);
});

test('suggestions prefer the same category and never include the product itself or a sold out one', function () {
    $category = Category::create(['name_ar' => 'ق', 'name_en' => 'C', 'slug' => 'c-'.uniqid()]);
    $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);
    $sameCategory = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);
    $soldOut = Product::factory()->outOfStock()->create(['category_id' => $category->id]);
    Product::factory()->create(['stock' => 5]);

    $this->get(route('products.show', $product->slug))->assertOk()
        ->assertViewHas('suggested', function ($suggested) use ($product, $sameCategory, $soldOut) {
            return $suggested->first()->id === $sameCategory->id
                && ! $suggested->contains('id', $product->id)
                && ! $suggested->contains('id', $soldOut->id);
        });
});

test('staff with edit permission can pick featured products from the list', function () {
    $product = Product::factory()->create();

    $this->actingAs(merchUser())->patch(route('dashboard.products.featured', $product))->assertForbidden();

    $this->actingAs(merchUser('admin'))->patch(route('dashboard.products.featured', $product))->assertRedirect();

    expect($product->fresh()->is_featured)->toBeTrue();
});

// ---------- Dashboard reports ----------

test('the dashboard reports highest stock and stagnant products, and hides them without permission', function () {
    Setting::saveValues(['stagnant_days' => 30]);

    $big = Product::factory()->create(['stock' => 900]);
    $old = Product::factory()->create(['stock' => 5]);
    Product::whereKey($old->id)->update(['created_at' => now()->subDays(60)]);
    $new = Product::factory()->create(['stock' => 7]);
    $soldOut = Product::factory()->outOfStock()->create();
    Product::whereKey($soldOut->id)->update(['created_at' => now()->subDays(60)]);

    $this->actingAs(merchUser('admin'))->get('/dashboard')->assertOk()
        ->assertViewHas('productReports', function (array $reports) use ($big, $old, $new, $soldOut) {
            return $reports['highestStock']->first()->id === $big->id
                && $reports['stagnant']->contains('id', $old->id)
                && ! $reports['stagnant']->contains('id', $new->id)
                && ! $reports['stagnant']->contains('id', $soldOut->id);
        });

    $this->actingAs(merchStaff([]))->get('/dashboard')->assertOk()
        ->assertViewHas('productReports', null)
        ->assertDontSee(__('app.highest_stock'));
});

test('a recent sale takes a product off the stagnant list', function () {
    $product = Product::factory()->create(['stock' => 5]);
    Product::whereKey($product->id)->update(['created_at' => now()->subDays(60)]);

    expect(Product::stagnant(30)->pluck('id')->all())->toContain($product->id);

    $user = merchUser();
    $order = Order::create(['user_id' => $user->id, 'status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => 1]);
    $order->items()->forceCreate(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'x', 'price' => 1, 'quantity' => 1]);

    expect(Product::stagnant(30)->pluck('id')->all())->not->toContain($product->id);
});

// ---------- Favorites ----------

test('favorites open in the store and a card can add and remove without a reload', function () {
    $user = merchUser();
    $product = Product::factory()->create(['stock' => 3]);

    $this->actingAs($user)->postJson("/favorites/{$product->id}")->assertOk()
        ->assertJson(['favorited' => true, 'count' => 1]);

    $this->actingAs($user)->get('/favorites')->assertOk()
        ->assertViewIs('front.favorites.index')
        ->assertSee($product->name)
        ->assertSee('class="breadcrumbs"', false)
        ->assertDontSee('main-sidebar', false);

    $this->actingAs($user)->postJson("/favorites/{$product->id}")->assertOk()
        ->assertJson(['favorited' => false, 'count' => 0]);

    $this->actingAs($user)->get('/favorites')->assertOk()->assertSee(__('app.no_favorites_text'));
});

test('guests are sent to log in from the heart', function () {
    Product::factory()->create(['stock' => 3]);

    $this->get('/')->assertOk()->assertSee('class="card-favorite"', false);
    $this->post('/favorites/1')->assertRedirect(route('login'));
});

// ---------- Notifications ----------

test('a customer is told when their order status changes, in their language', function () {
    $customer = merchUser();
    $order = Order::create(['user_id' => $customer->id, 'status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => 1]);

    $this->actingAs(merchUser('admin'))->putJson(route('dashboard.orders.update', $order), ['status' => 'processing'])->assertOk();

    expect($customer->fresh()->unreadNotifications)->toHaveCount(1);

    $this->actingAs($customer)->get('/notifications')->assertOk()
        ->assertViewIs('notifications.index')
        ->assertDontSee('main-sidebar', false)
        ->assertSee(__('app.order_status_changed', ['number' => $order->number, 'status' => __('app.processing')]));
});

test('opening a notification marks it read and goes to the order', function () {
    $customer = merchUser();
    $order = Order::create(['user_id' => $customer->id, 'status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => 1]);
    $customer->notify(new OrderStatusChangedNotification($order));
    $notification = $customer->notifications()->first();

    $this->actingAs($customer)->get(route('notifications.open', $notification->id))
        ->assertRedirect(route('front.orders.show', $order));

    expect($notification->fresh()->read_at)->not->toBeNull();

    $admin = merchUser('admin');
    $admin->notify(new OrderStatusChangedNotification($order));

    $this->actingAs($admin)->get(route('notifications.open', $admin->notifications()->first()->id))
        ->assertRedirect(route('dashboard.orders.show', $order));
});

test('notifications can be filtered and deleted, and only by their owner', function () {
    $customer = merchUser();
    $other = merchUser();
    $order = Order::create(['user_id' => $customer->id, 'status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => 1]);
    $customer->notify(new OrderStatusChangedNotification($order));
    $customer->notify(new OrderStatusChangedNotification($order));
    [$first, $second] = $customer->notifications()->get();
    $first->markAsRead();

    $this->actingAs($customer)->get('/notifications?filter=unread')->assertOk()
        ->assertViewHas('notifications', fn ($notifications) => $notifications->count() === 1);

    $this->actingAs($other)->delete(route('notifications.destroy', $second->id))->assertNotFound();
    $this->actingAs($other)->get(route('notifications.open', $second->id))->assertNotFound();

    $this->actingAs($customer)->delete(route('notifications.destroyRead'))->assertRedirect();
    expect($customer->notifications()->count())->toBe(1);

    $this->actingAs($customer)->delete(route('notifications.destroy', $second->id))->assertRedirect();
    expect($customer->notifications()->count())->toBe(0);
});

test('staff read notifications inside the dashboard', function () {
    $this->actingAs(merchStaff([]))->get('/dashboard/notifications')->assertOk()
        ->assertViewIs('dashboard.notifications.index')
        ->assertSee('main-sidebar', false);
});

// ---------- Add product ----------

function productPayload(array $overrides = []): array
{
    return $overrides + [
        'name_ar' => 'منتج', 'name_en' => 'Same Name', 'description' => str_repeat('وصف طويل ', 100),
        'status' => 'active', 'category_id' => Category::create(['name_ar' => 'ق', 'name_en' => 'C', 'slug' => 'c-'.uniqid()])->id,
        'product_type' => 'simple', 'price' => '19.99', 'compare_price' => '29.99', 'stock' => 4, 'is_featured' => '1',
    ];
}

test('the product form accepts decimals, long descriptions, featured, and repeated names', function () {
    $admin = merchUser('admin');

    $this->actingAs($admin)->get('/dashboard/products/create')->assertOk()->assertSee(__('app.product_pricing_stock'));

    $this->actingAs($admin)->post('/dashboard/products', productPayload())->assertRedirect('/dashboard/products');
    $this->actingAs($admin)->post('/dashboard/products', productPayload())->assertRedirect('/dashboard/products');

    $products = Product::orderBy('id')->get();

    expect($products)->toHaveCount(2)
        ->and($products[0]->slug)->toBe('same-name')
        ->and($products[1]->slug)->toBe('same-name-2')
        ->and((float) $products[0]->price)->toBe(19.99)
        ->and($products[0]->is_featured)->toBeTrue();
});

test('a product form with mistakes comes back with the messages', function () {
    $this->actingAs(merchUser('admin'))
        ->from('/dashboard/products/create')
        ->post('/dashboard/products', productPayload(['name_en' => '', 'price' => 'abc']))
        ->assertRedirect('/dashboard/products/create')
        ->assertSessionHasErrors(['name_en', 'price']);

    expect(Product::count())->toBe(0);
});

test('saving a product without the featured box takes it off the picks', function () {
    $admin = merchUser('admin');
    $product = Product::factory()->create(['is_featured' => true, 'stock' => 2]);

    $this->actingAs($admin)->get("/dashboard/products/{$product->id}/edit")->assertOk();

    $this->actingAs($admin)->put("/dashboard/products/{$product->id}", [
        'name_ar' => 'a', 'name_en' => 'b', 'status' => 'active', 'category_id' => $product->category_id,
        'product_type' => 'simple', 'price' => 10, 'stock' => 2,
    ])->assertRedirect();

    expect($product->fresh()->is_featured)->toBeFalse();
});

// ---------- Social media, top bar, and dark mode ----------

test('social links show where the settings say', function () {
    Setting::saveValues(['youtube' => 'https://youtube.com/mystore', 'tiktok' => 'https://tiktok.com/@mystore', 'whatsapp' => null]);

    $this->get('/')->assertOk()->assertSee('https://youtube.com/mystore')->assertSee('https://tiktok.com/@mystore');
    $this->get(route('contact'))->assertOk()->assertSee('https://youtube.com/mystore');

    Setting::saveValues(['show_social_header' => '0', 'show_social_footer' => '0']);

    $this->get('/')->assertOk()->assertDontSee('https://youtube.com/mystore');
    $this->get(route('contact'))->assertOk()->assertSee('https://youtube.com/mystore');
});

test('a social link must be a web address', function () {
    $base = [
        'site_name' => 'S', 'tax' => 0, 'payment_cash_enabled' => '1', 'currency' => 'USD', 'low_stock_threshold' => 5,
        'products_per_page' => 12, 'default_theme' => 'light', 'stagnant_days' => 30,
    ];

    $this->actingAs(merchUser('admin'))->put(route('dashboard.settings.update'), $base + ['youtube' => 'javascript:alert(1)'])
        ->assertSessionHasErrors('youtube');
});

test('the top bar holds the information pages and the main menu holds the shop', function () {
    $html = $this->get('/')->assertOk()->getContent();

    preg_match('/<nav class="topbar-pages".*?<\/nav>/s', $html, $topbar);

    expect($topbar[0])
        ->toContain(route('pages.about'))
        ->toContain(route('contact'))
        ->toContain(route('pages.faq'))
        ->toContain(route('front.posts.index'));

    $this->get('/')->assertSee(route('bundles.index'));
});

test('dark mode can be offered or turned off', function () {
    $this->get('/')->assertOk()->assertSee('data-theme-toggle', false)->assertSee('dark-store.css');

    $this->actingAs(merchUser('admin'))->get('/dashboard')->assertOk()->assertSee('data-theme-toggle', false);

    Setting::saveValues(['dark_mode_enabled' => '0']);

    auth()->logout();

    $this->get('/')->assertOk()->assertDontSee('topbar-theme', false);
});

// ---------- Variations table ----------

function variationPayload(array $valueIds, array $overrides = []): array
{
    return $overrides + ['attribute_value_ids' => $valueIds, 'price' => 100, 'stock' => 5, 'sku' => 'SKU-'.uniqid()];
}

test('a variable product saves two attributes per variation and its primary', function () {
    $color = App\Models\Attribute::create(['name_en' => 'Color', 'name_ar' => 'اللون']);
    $size = App\Models\Attribute::create(['name_en' => 'Size', 'name_ar' => 'المقاس']);
    $red = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Red']);
    $small = App\Models\AttributeValue::create(['attribute_id' => $size->id, 'value' => 'S']);
    $large = App\Models\AttributeValue::create(['attribute_id' => $size->id, 'value' => 'L']);

    $payload = productPayload(['product_type' => 'variable', 'primary' => 1, 'variations' => [
        variationPayload([$red->id, $small->id]),
        variationPayload([$red->id, $large->id]),
    ]]);
    unset($payload['price'], $payload['compare_price'], $payload['stock']);

    $this->actingAs(merchUser('admin'))->post('/dashboard/products', $payload)->assertRedirect('/dashboard/products')->assertSessionHasNoErrors();

    $variations = Product::first()->variations()->with('values')->orderBy('id')->get();

    expect($variations)->toHaveCount(2)
        ->and($variations[0]->values->pluck('id')->sort()->values()->all())->toBe(collect([$red->id, $small->id])->sort()->values()->all())
        ->and((bool) $variations[0]->is_primary)->toBeFalse()
        ->and((bool) $variations[1]->is_primary)->toBeTrue();
});

test('adding an attribute to a saved variation keeps the same variation', function () {
    $admin = merchUser('admin');
    $product = Product::factory()->variable(1)->create();
    $variation = $product->variations()->first();
    $size = $variation->values()->first();
    $color = App\Models\Attribute::create(['name_en' => 'Color', 'name_ar' => 'اللون']);
    $red = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Red']);
    $blue = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Blue']);

    $this->actingAs($admin)->put("/dashboard/products/{$product->id}", [
        'name_ar' => 'a', 'name_en' => 'b', 'status' => 'active', 'category_id' => $product->category_id,
        'product_type' => 'variable', 'primary' => 0,
        'variations' => [
            variationPayload([$size->id, $red->id], ['id' => $variation->id]),
            variationPayload([$size->id, $blue->id]),
        ],
    ])->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->variations()->count())->toBe(2)
        ->and($product->variations()->whereKey($variation->id)->exists())->toBeTrue()
        ->and($variation->fresh()->values->pluck('id')->all())->toContain($red->id);
});

test('a form with a bad variation comes back with its rows instead of an empty table', function () {
    $color = App\Models\Attribute::create(['name_en' => 'Color', 'name_ar' => 'اللون']);
    $red = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Red']);
    $admin = merchUser('admin');

    $payload = productPayload(['product_type' => 'variable', 'primary' => 0, 'variations' => [
        ['attribute_value_ids' => [$red->id], 'price' => '77.50', 'stock' => '', 'sku' => 'KEEP-ME'],
    ]]);

    $this->actingAs($admin)->from('/dashboard/products/create')->post('/dashboard/products', $payload)
        ->assertRedirect('/dashboard/products/create')
        ->assertSessionHasErrors('variations.0.stock');

    $this->actingAs($admin)->get('/dashboard/products/create')->assertOk()
        ->assertSee('KEEP-ME')
        ->assertSee('77.50');
});

test('a variation photo must be an image', function () {
    $color = App\Models\Attribute::create(['name_en' => 'Color', 'name_ar' => 'اللون']);
    $red = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Red']);

    $payload = productPayload(['product_type' => 'variable', 'primary' => 0, 'variations' => [
        variationPayload([$red->id], ['images' => [Illuminate\Http\UploadedFile::fake()->create('evil.php', 10, 'text/x-php')]]),
    ]]);

    $this->actingAs(merchUser('admin'))->post('/dashboard/products', $payload)->assertSessionHasErrors('variations.0.images.0');
});

test('editing a product keeps its address unless the name changes', function () {
    $admin = merchUser('admin');
    $first = Product::factory()->create(['name_en' => 'Same', 'slug' => 'same', 'stock' => 1]);
    $second = Product::factory()->create(['name_en' => 'Same', 'slug' => 'same-2', 'stock' => 1]);
    $edit = fn (string $name) => ['name_ar' => 'a', 'name_en' => $name, 'status' => 'active', 'category_id' => $second->category_id, 'product_type' => 'simple', 'price' => 5, 'stock' => 1];

    $this->actingAs($admin)->put("/dashboard/products/{$second->id}", $edit('Same'))->assertSessionHasNoErrors();
    expect($second->fresh()->slug)->toBe('same-2');

    $this->actingAs($admin)->put("/dashboard/products/{$second->id}", $edit('Brand New'))->assertSessionHasNoErrors();
    expect($second->fresh()->slug)->toBe('brand-new');

    $this->actingAs($admin)->put("/dashboard/products/{$second->id}", $edit('Same'))->assertSessionHasNoErrors();
    expect($second->fresh()->slug)->toBe('same-2');
});

// ---------- Sections on the product page ----------

test('the product page shows picks, best sellers, and bundles without repeating a product', function () {
    $category = Category::create(['name_ar' => 'ق', 'name_en' => 'C', 'slug' => 'c-'.uniqid()]);
    $otherCategory = Category::create(['name_ar' => 'ع', 'name_en' => 'O', 'slug' => 'o-'.uniqid()]);
    $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5, 'is_featured' => true]);

    // Four in stock fill the suggestions, so the next ones reach the other rows.
    $suggested = Product::factory()->count(4)->create(['category_id' => $category->id, 'stock' => 5]);
    $picked = Product::factory()->create(['category_id' => $otherCategory->id, 'stock' => 5, 'is_featured' => true]);
    $seller = Product::factory()->create(['category_id' => $otherCategory->id, 'stock' => 5]);
    $pickedAndSold = Product::factory()->create(['category_id' => $otherCategory->id, 'stock' => 5, 'is_featured' => true]);

    // These are older, so they don't take the suggestions' places.
    Product::whereIn('id', [$picked->id, $seller->id, $pickedAndSold->id])->update(['created_at' => now()->subDays(10)]);

    $user = merchUser();
    $order = Order::create(['user_id' => $user->id, 'status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => 1]);

    foreach ([$seller, $pickedAndSold, $product] as $sold) {
        $order->items()->forceCreate(['order_id' => $order->id, 'product_id' => $sold->id, 'product_name' => 'x', 'price' => 1, 'quantity' => 3]);
    }

    $other = makeBundle([$picked, $seller], [[2, 'percent', 10]], ['name_ar' => 'Other Set', 'name_en' => 'Other Set']);
    $own = makeBundle([$product, $picked], [[2, 'percent', 20]], ['name_ar' => 'Own Set', 'name_en' => 'Own Set']);
    $own->update(['created_at' => now()->subDays(5)]);

    $this->get(route('products.show', $product->slug))->assertOk()
        ->assertSee(__('app.featured_products_text'))
        ->assertSee(__('app.best_sellers_text'))
        ->assertViewHas('featured', fn ($rows) => $rows->pluck('id')->contains($picked->id)
            && $rows->pluck('id')->contains($pickedAndSold->id)
            && ! $rows->pluck('id')->contains($product->id))
        ->assertViewHas('bestSellers', fn ($rows) => $rows->pluck('id')->all() === [$seller->id])
        ->assertViewHas('bundles', fn ($rows) => $rows->first()->is($own) && $rows->last()->is($other))
        ->assertViewHas('suggested', fn ($rows) => $rows->pluck('id')->sort()->values()->all() === $suggested->pluck('id')->sort()->values()->all());
});

test('the product page sections stay hidden when they have nothing to show', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $this->get(route('products.show', $product->slug))->assertOk()
        ->assertDontSee(__('app.featured_products_text'))
        ->assertDontSee(__('app.best_sellers_text'))
        ->assertDontSee(__('app.bundles_text'));
});

// ---------- Products list: how many per page ----------

test('visitors choose how many products a page shows, and the choice stays for the session', function () {
    Product::factory()->count(30)->create(['stock' => 1]);

    $perPage = fn ($response) => $response->assertOk()->viewData('products')->perPage();

    expect($perPage($this->get('/front/products')))->toBe(12)
        ->and($perPage($this->get('/front/products?per_page=24')))->toBe(24)
        ->and($perPage($this->get('/front/products')))->toBe(24);

    $this->get('/front/products')->assertSee('<option value="24" selected', false);
});

test('a number that is not on the list is ignored', function (string $value) {
    Product::factory()->count(3)->create(['stock' => 1]);

    expect($this->get("/front/products?per_page={$value}")->assertOk()->viewData('products')->perPage())->toBe(12);
})->with(['100000', '0', '-5', 'abc', '13']);

test('the store default from the settings is offered and used', function () {
    Setting::saveValues(['products_per_page' => 9]);
    Product::factory()->count(3)->create(['stock' => 1]);

    $response = $this->get('/front/products')->assertOk()->assertSee('<option value="9" selected', false);

    expect($response->viewData('products')->perPage())->toBe(9)
        ->and($response->viewData('perPageOptions'))->toBe([9, 12, 24, 36, 48]);
});

test('page links keep the filters and no longer invent a search', function () {
    Product::factory()->count(30)->create(['stock' => 1]);

    $html = $this->get('/front/products?per_page=12&sort_by=newest', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->getContent();

    expect($html)->toContain('per_page=12')
        ->toContain('sort_by=newest')
        ->toContain('page=2')
        ->not->toContain('search=1')
        ->toContain('id="products-range"');
});

test('the list view text comes from the description and is safe', function () {
    Product::factory()->create(['stock' => 1, 'description' => '<script>alert(1)</script>Soft cotton']);

    $this->get('/front/products')->assertOk()
        ->assertSee('product-card-excerpt', false)
        ->assertSee('Soft cotton')
        ->assertDontSee('<script>alert(1)</script>', false);
});

// ---------- Cash on delivery limits ----------

test('cash on delivery stays available up to the item limit', function () {
    Setting::saveValues(['cod_max_items' => 3]);
    $product = Product::factory()->create(['price' => 10, 'stock' => 50]);
    $user = merchUser();

    addToCart($this, $user, $product, 3);

    $this->actingAs($user)->get('/checkout')->assertOk()
        ->assertSee('id="payment-cash"', false)
        ->assertDontSee(__('app.cod_limit_items', ['count' => 3]));
});

test('cash on delivery is refused above the item limit, counting quantities', function () {
    Setting::saveValues(['cod_max_items' => 3]);
    $product = Product::factory()->create(['price' => 10, 'stock' => 50]);
    $user = merchUser();

    addToCart($this, $user, $product, 4);

    $this->actingAs($user)->get('/checkout')->assertOk()
        ->assertDontSee('id="payment-cash"', false)
        ->assertSee('id="payment-visa"', false)
        ->assertSee(__('app.cod_limit_items', ['count' => 3]));

    // Sending it anyway does not get around the page.
    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect()->assertSessionHas('error');

    expect(Order::count())->toBe(0);

    $this->actingAs($user)->post('/checkout', ['payment_method' => 'visa'] + checkoutData())->assertRedirect();

    expect(Order::first()->payment_method)->toBe(App\Enums\PaymentMethod::VISA);
});

test('cash on delivery is refused above the amount limit, and exactly the limit is allowed', function () {
    Setting::saveValues(['cod_max_amount' => 100]);
    $product = Product::factory()->create(['price' => 100, 'stock' => 50]);
    $user = merchUser();

    addToCart($this, $user, $product);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertRedirect();
    expect(Order::count())->toBe(1);

    addToCart($this, $user, $product);
    addToCart($this, $user, $product);

    $this->actingAs($user)->get('/checkout')->assertOk()
        ->assertDontSee('id="payment-cash"', false)
        ->assertSee(__('app.cod_limit_amount', ['amount' => App\Helpers\Currency::format(100)]));

    $this->actingAs($user)->post('/checkout', checkoutData())->assertSessionHas('error');

    expect(Order::count())->toBe(1);
});

test('a coupon or bundle does not change the limit, which uses the price before discounts', function () {
    Setting::saveValues(['cod_max_amount' => 150]);
    $products = Product::factory()->count(2)->create(['price' => 100, 'stock' => 10]);
    makeBundle($products, [[2, 'percent', 50]]);
    $user = merchUser();

    addToCart($this, $user, $products[0]);
    addToCart($this, $user, $products[1]);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

test('with cash blocked and no other method, the order cannot be placed and the customer is told what to do', function () {
    Setting::saveValues(['cod_max_items' => 1, 'payment_card_enabled' => '0']);
    $product = Product::factory()->create(['price' => 10, 'stock' => 50]);
    $user = merchUser();

    addToCart($this, $user, $product, 2);

    $this->actingAs($user)->get('/checkout')->assertOk()
        ->assertSee(__('app.cod_no_method_contact'))
        ->assertSee(route('contact'))
        ->assertSee('disabled', false);

    $this->actingAs($user)->post('/checkout', checkoutData())->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

test('no limits means nothing changes, and the settings page saves and checks them', function () {
    $product = Product::factory()->create(['price' => 99999, 'stock' => 500]);
    $user = merchUser();
    addToCart($this, $user, $product, 200);

    $this->actingAs($user)->get('/checkout')->assertOk()->assertSee('id="payment-cash"', false);

    $base = [
        'site_name' => 'S', 'tax' => 0, 'payment_cash_enabled' => '1', 'currency' => 'USD', 'low_stock_threshold' => 5,
        'products_per_page' => 12, 'default_theme' => 'light', 'stagnant_days' => 30,
    ];
    $admin = merchUser('admin');

    $this->actingAs($admin)->get(route('dashboard.settings.edit'))->assertOk()->assertSee('name="cod_max_items"', false);

    $this->actingAs($admin)->put(route('dashboard.settings.update'), $base + ['cod_max_items' => 0, 'cod_max_amount' => -1])
        ->assertSessionHasErrors(['cod_max_items', 'cod_max_amount']);

    $this->actingAs($admin)->put(route('dashboard.settings.update'), $base + ['cod_max_items' => 5, 'cod_max_amount' => 2500])
        ->assertSessionHasNoErrors();

    expect((int) Setting::get('cod_max_items'))->toBe(5)
        ->and((float) Setting::get('cod_max_amount'))->toBe(2500.0);

    $this->actingAs($admin)->put(route('dashboard.settings.update'), $base + ['cod_max_items' => '', 'cod_max_amount' => ''])
        ->assertSessionHasNoErrors();

    expect(Setting::get('cod_max_items'))->toBeNull();
});
