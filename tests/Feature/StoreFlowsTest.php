<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Coupon;
use App\Models\Governorate;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\GovernorateSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(GovernorateSeeder::class);
});

function userWithRole(string $role = 'user'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', $role)->firstOrFail()->id);

    return $user;
}

function staffWith(array $permissions = ['access-dashboard']): User
{
    $role = Role::create(['name' => 'staff-'.uniqid()]);
    $role->permissions()->sync(Permission::whereIn('name', $permissions)->pluck('id'));
    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    return $user;
}

function makeCategory(): Category
{
    $slug = 'cat-'.uniqid();

    return Category::forceCreate(['name_ar' => $slug, 'name_en' => $slug, 'slug' => $slug, 'status' => 'active']);
}

function makeProduct(array $attributes = []): Product
{
    $slug = 'prod-'.uniqid();

    return Product::forceCreate($attributes + [
        'name_ar' => $slug, 'name_en' => $slug, 'slug' => $slug, 'description' => 'd', 'status' => 'active',
        'product_type' => 'simple', 'price' => 100, 'stock' => 10, 'sku' => $slug,
        'category_id' => makeCategory()->id, 'user_id' => User::factory()->create()->id,
    ]);
}

function makeVariation(Product $product, float $price, int $stock = 10): ProductVariation
{
    return ProductVariation::forceCreate([
        'product_id' => $product->id, 'price' => $price, 'stock' => $stock, 'sku' => 'var-'.uniqid(),
        'is_primary' => 1, 'is_active' => 1,
    ]);
}

function makeCoupon(array $attributes = []): Coupon
{
    return Coupon::forceCreate($attributes + ['code' => 'C'.uniqid(), 'type' => 'percent', 'value' => 50]);
}

function checkoutAddress(): array
{
    return [
        'first_name' => 'a', 'last_name' => 'b', 'email' => 'a@b.test', 'phone_number' => '1', 'city' => 'x',
        'street_address' => 'y', 'country' => 'EG', 'governorate_id' => Governorate::first()->id, 'payment_method' => 'cash',
    ];
}

function placeOrder($test, User $user, Product $product, int $quantity = 1): Order
{
    $test->actingAs($user)->postJson('/cart', ['product_id' => $product->id, 'quantity' => $quantity])->assertCreated();
    $test->actingAs($user)->post('/checkout', checkoutAddress())->assertRedirect();

    return Order::where('user_id', $user->id)->latest('id')->firstOrFail();
}

test('a customer can check out and see the order pages', function () {
    $user = userWithRole();
    $product = makeProduct(['price' => 100, 'stock' => 10]);
    $governorate = Governorate::first();

    $order = placeOrder($this, $user, $product, 3);

    expect($product->fresh()->stock)->toBe(7)
        ->and((float) $order->total)->toBe(300 + (float) $governorate->shipping_price + (float) config('shop.flat_tax'))
        ->and(Cart::where('user_id', $user->id)->count())->toBe(0);

    $this->actingAs($user)->get(route('front.orders.success', $order))->assertOk();
    $this->actingAs($user)->get('/my-orders')->assertOk();
    $this->actingAs($user)->get(route('front.orders.show', $order))->assertOk();
});

test('customers cannot see other customers orders', function () {
    $order = placeOrder($this, userWithRole(), makeProduct());
    $other = userWithRole();

    $this->actingAs($other)->get(route('front.orders.success', $order))->assertForbidden();
    $this->actingAs($other)->get(route('front.orders.show', $order))->assertForbidden();
});

test('a notification failure does not fail a saved order', function () {
    config(['broadcasting.default' => 'pusher', 'broadcasting.connections.pusher' => [
        'driver' => 'pusher', 'key' => 'k', 'secret' => 's', 'app_id' => '1',
        'options' => ['host' => '127.0.0.1', 'port' => 1, 'scheme' => 'http', 'useTLS' => false, 'cluster' => 'eu'],
        'client_options' => ['timeout' => 2],
    ]]);
    $user = userWithRole();
    $product = makeProduct();

    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertCreated();
    $response = $this->actingAs($user)->post('/checkout', checkoutAddress());

    $order = Order::where('user_id', $user->id)->firstOrFail();
    $response->assertRedirect(route('front.orders.success', $order));
    expect($product->fresh()->stock)->toBe(9);
});

test('checkout stops when stock runs out after the item was added', function () {
    $user = userWithRole();
    $product = makeProduct(['stock' => 5]);
    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 5])->assertCreated();
    $product->update(['stock' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutAddress())->assertSessionHas('error');

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(1)
        ->and(Cart::where('user_id', $user->id)->count())->toBe(1);
});

test('canceling restores stock once and refunding a paid order marks it refunded', function () {
    $admin = userWithRole('admin');
    $product = makeProduct(['stock' => 10]);
    $variable = makeProduct(['product_type' => 'variable']);
    $variation = makeVariation($variable, 50, 10);
    $customer = userWithRole();

    $order = placeOrder($this, $customer, $product, 3);
    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $order), ['status' => 'canceled'])->assertOk();
    expect($product->fresh()->stock)->toBe(10);
    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $order), ['status' => 'refunded'])->assertStatus(422);
    expect($product->fresh()->stock)->toBe(10);

    $this->actingAs($customer)->postJson('/cart', ['product_id' => $variable->id, 'variation_id' => $variation->id, 'quantity' => 2])->assertCreated();
    $this->actingAs($customer)->post('/checkout', checkoutAddress());
    $second = Order::latest('id')->first();
    expect($variation->fresh()->stock)->toBe(8);

    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $second), ['status' => 'completed'])->assertOk();
    expect($second->fresh()->payment_status)->toBe(PaymentStatus::PAID);
    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $second), ['status' => 'pending'])->assertStatus(422);
    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $second), ['status' => 'refunded'])->assertOk();
    expect($second->fresh()->payment_status)->toBe(PaymentStatus::REFUNDED)
        ->and($variation->fresh()->stock)->toBe(10);
});

test('only closed orders can be deleted', function () {
    $admin = userWithRole('admin');
    $order = placeOrder($this, userWithRole(), makeProduct());

    $this->actingAs($admin)->delete(route('dashboard.orders.destroy', $order))->assertSessionHas('error');
    expect(Order::whereKey($order->id)->exists())->toBeTrue();

    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $order), ['status' => 'canceled'])->assertOk();
    $this->actingAs($admin)->delete(route('dashboard.orders.destroy', $order))->assertSessionHas('success');
    expect(Order::whereKey($order->id)->exists())->toBeFalse();
});

test('expired pending orders are removed and their stock returned', function () {
    $product = makeProduct(['stock' => 10]);
    $order = placeOrder($this, userWithRole(), $product, 4);
    Order::whereKey($order->id)->update(['created_at' => now()->subDays(40)]);

    app()->call([new App\Jobs\DeleteExpiredOrders, 'handle']);

    expect(Order::whereKey($order->id)->exists())->toBeFalse()
        ->and($product->fresh()->stock)->toBe(10);
});

test('a variation from another product cannot set the price', function () {
    $user = userWithRole();
    $expensive = makeProduct(['price' => 1000, 'product_type' => 'variable']);
    makeVariation($expensive, 1000);
    $cheapVariation = makeVariation(makeProduct(['price' => 1, 'product_type' => 'variable']), 1);

    $this->actingAs($user)
        ->postJson('/cart', ['product_id' => $expensive->id, 'variation_id' => $cheapVariation->id])
        ->assertStatus(422);

    expect(Cart::where('user_id', $user->id)->count())->toBe(0);
});

test('a variable product needs a variation', function () {
    $product = makeProduct(['product_type' => 'variable']);
    makeVariation($product, 10);

    $this->actingAs(userWithRole())->postJson('/cart', ['product_id' => $product->id])->assertStatus(422);
});

test('the cart enforces stock on add and update', function () {
    $user = userWithRole();
    $product = makeProduct(['stock' => 5]);

    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 6])->assertStatus(422);
    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 3])->assertCreated();
    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 3])->assertStatus(422);

    $row = Cart::where('user_id', $user->id)->firstOrFail();
    $this->actingAs($user)->patchJson("/cart/{$row->id}", ['quantity' => 40])->assertStatus(422);
    expect($row->fresh()->quantity)->toBe(3);
    $this->actingAs($user)->patchJson("/cart/{$row->id}", ['quantity' => 5])->assertOk();
    expect($row->fresh()->quantity)->toBe(5);
});

test('the cart count stays right after adding another product', function () {
    $user = userWithRole();
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct()->id])->assertJsonPath('count', 1);
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct()->id])->assertJsonPath('count', 2);
});

test('a guest cart moves to the account on login and does not stay visible after logout', function () {
    $password = 'password';
    $user = userWithRole();
    $shared = makeProduct();
    Illuminate\Support\Facades\DB::table('carts')->insert(['id' => (string) str()->uuid(), 'cookie_id' => (string) str()->uuid(), 'user_id' => $user->id, 'product_id' => $shared->id, 'quantity' => 1]);
    $cookie = (string) str()->uuid();
    Illuminate\Support\Facades\DB::table('carts')->insert(['id' => (string) str()->uuid(), 'cookie_id' => $cookie, 'product_id' => $shared->id, 'quantity' => 2]);

    $this->withCookie('cart_id', $cookie)
        ->post('/login', ['email' => $user->email, 'password' => $password])
        ->assertRedirect();

    expect(Cart::where('user_id', $user->id)->sum('quantity'))->toEqual(3)
        ->and(Cart::where('user_id', $user->id)->count())->toBe(1)
        ->and(Cart::whereNull('user_id')->count())->toBe(0);

    auth()->logout();
    $this->withCookie('cart_id', $cookie)->getJson('/cart/json')->assertJsonPath('count', 0);
});

test('invalid coupons are rejected without a server error', function (array $attributes) {
    $user = userWithRole();
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct(['price' => 100])->id])->assertCreated();
    $coupon = makeCoupon($attributes);

    $this->actingAs($user)
        ->postJson('/checkout/apply-coupon', ['code' => $coupon->code])
        ->assertStatus(422)
        ->assertJsonStructure(['error']);
})->with([
    'inactive' => [['is_active' => false]],
    'expired' => [['expires_at' => now()->subDay()]],
    'used up' => [['usage_limit' => 1, 'used_count' => 1]],
    'below minimum' => [['min_order_amount' => 99999]],
]);

test('an unknown coupon returns a message', function () {
    $user = userWithRole();
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct()->id])->assertCreated();

    $this->actingAs($user)->postJson('/checkout/apply-coupon', ['code' => 'NOPE'])
        ->assertStatus(422)->assertJsonStructure(['error']);
});

test('valid coupons discount the order and count their use', function () {
    $user = userWithRole();
    $product = makeProduct(['price' => 100]);
    $coupon = makeCoupon(['usage_limit' => 1]);
    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertCreated();

    $this->actingAs($user)->postJson('/checkout/apply-coupon', ['code' => $coupon->code])
        ->assertOk()->assertJsonPath('totals.discount', 50);
    $this->actingAs($user)->post('/checkout', checkoutAddress())->assertRedirect();

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect((float) $order->discount)->toBe(50.0)
        ->and($coupon->fresh()->used_count)->toBe(1);
});

test('checkout refuses a coupon that stopped being valid', function () {
    $user = userWithRole();
    $coupon = makeCoupon();
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct()->id])->assertCreated();
    $this->actingAs($user)->postJson('/checkout/apply-coupon', ['code' => $coupon->code])->assertOk();
    $coupon->update(['is_active' => false]);

    $this->actingAs($user)->post('/checkout', checkoutAddress())->assertSessionHas('error');
    expect(Order::count())->toBe(0);
});

test('free shipping coupons can be created and remove shipping', function () {
    $admin = userWithRole('admin');
    $this->actingAs($admin)->post('/dashboard/coupons', [
        'code' => 'SHIPFREE', 'type' => 'free_shipping', 'is_active' => 1,
    ])->assertSessionHasNoErrors();

    $user = userWithRole();
    $product = makeProduct(['price' => 100]);
    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertCreated();
    $this->actingAs($user)->postJson('/checkout/apply-coupon', ['code' => 'SHIPFREE', 'governorate_id' => Governorate::first()->id])
        ->assertOk()->assertJsonPath('totals.shipping', 0);
});

test('the admin coupon form saves status and expiry', function () {
    $this->actingAs(userWithRole('admin'))->post('/dashboard/coupons', [
        'code' => 'OFF', 'type' => 'percent', 'value' => 10, 'is_active' => 0,
        'expires_at' => now()->addYear()->toDateString(),
    ])->assertSessionHasNoErrors();

    $coupon = Coupon::where('code', 'OFF')->firstOrFail();
    expect($coupon->is_active)->toBeFalse()
        ->and($coupon->expires_at->toDateString())->toBe(now()->addYear()->toDateString());
});

test('users can only delete their own comments', function () {
    $owner = userWithRole();
    $post = Post::forceCreate(['title' => 't', 'content' => 'c', 'slug' => 'p-'.uniqid(), 'user_id' => $owner->id, 'status' => 'published']);
    $comment = Comment::forceCreate(['user_id' => $owner->id, 'post_id' => $post->id, 'body' => 'b']);

    $this->actingAs(userWithRole())->deleteJson("/comments/{$comment->id}")->assertForbidden();
    expect(Comment::whereKey($comment->id)->exists())->toBeTrue();

    $this->actingAs($owner)->deleteJson("/comments/{$comment->id}")->assertOk();
    expect(Comment::whereKey($comment->id)->exists())->toBeFalse();
});

test('dashboard access alone does not unlock management pages', function () {
    $staff = staffWith();

    foreach (['users', 'roles', 'orders', 'coupons', 'brands', 'tags', 'posts', 'governorates', 'attributes', 'attribute-values', 'categories', 'products'] as $section) {
        $this->actingAs($staff)->get("/dashboard/{$section}")->assertForbidden();
    }
});

test('each permission unlocks its own section', function (string $permission, string $section) {
    $this->actingAs(staffWith(['access-dashboard', $permission]))->get("/dashboard/{$section}")->assertOk();
})->with([
    ['manage-users', 'users'],
    ['manage-roles', 'roles'],
    ['view-order', 'orders'],
    ['manage-coupons', 'coupons'],
    ['manage-brands', 'brands'],
    ['manage-tags', 'tags'],
    ['manage-posts', 'posts'],
    ['manage-governorates', 'governorates'],
    ['manage-attributes', 'attributes'],
    ['manage-attributes', 'attribute-values'],
    ['view-categories', 'categories'],
    ['view-product', 'products'],
]);

test('user managers cannot create admins or change admins', function () {
    $staff = staffWith(['access-dashboard', 'manage-users']);
    $adminRole = Role::where('name', 'admin')->firstOrFail();
    $admin = userWithRole('admin');

    $this->actingAs($staff)->put("/dashboard/users/{$staff->id}", ['name' => 'x', 'email' => $staff->email, 'role_id' => $adminRole->id])->assertForbidden();
    expect($staff->fresh()->hasRole('admin'))->toBeFalse();

    $this->actingAs($staff)->delete("/dashboard/users/{$admin->id}")->assertForbidden();
    expect(User::whereKey($admin->id)->exists())->toBeTrue();

    $this->actingAs($admin)->delete("/dashboard/users/{$admin->id}")->assertSessionHas('error');
    expect(User::whereKey($admin->id)->exists())->toBeTrue();
});

test('customers cannot enter the dashboard', function () {
    $this->actingAs(userWithRole())->get('/dashboard')->assertForbidden();
});

test('unpublished products stay private', function () {
    $draft = makeProduct(['status' => 'draft', 'name_en' => 'HIDDENDRAFT', 'name_ar' => 'HIDDENDRAFT']);

    expect($this->get('/front/products')->getContent())->not->toContain('HIDDENDRAFT');
    $this->get("/front/product/{$draft->slug}")->assertNotFound();
    expect($this->getJson('/api/products')->getContent())->not->toContain('HIDDENDRAFT');
    $this->getJson("/api/products/{$draft->id}")->assertNotFound();
    $this->actingAs(userWithRole())->postJson('/cart', ['product_id' => $draft->id])->assertStatus(422);

    $this->actingAs(userWithRole('admin'))->get("/front/product/{$draft->slug}")->assertOk();
});

test('unpublished posts stay private', function () {
    $post = Post::forceCreate(['title' => 'HIDDENPOST', 'content' => 'c', 'slug' => 'p-'.uniqid(), 'user_id' => userWithRole()->id, 'status' => 'archived']);

    expect($this->get('/posts')->getContent())->not->toContain('HIDDENPOST');
    $this->get("/posts/{$post->id}")->assertNotFound();
});

test('a deleted product in the cart does not break the store', function () {
    $user = userWithRole();
    $product = makeProduct();
    $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertCreated();
    $product->delete();

    $this->actingAs($user)->get('/cart')->assertOk();
    $this->actingAs($user)->getJson('/cart/json')->assertOk()->assertJsonPath('count', 0);
});

test('the category menu lists every category on any page and stays cheap', function () {
    foreach (range(1, 20) as $i) {
        makeCategory();
    }

    $queries = 0;
    Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries) {
        if (str_contains($query->sql, 'from `categories`')) {
            $queries++;
        }
    });

    $content = $this->get('/?page=99')->assertOk()->getContent();

    expect(substr_count($content, '<option value="'))->toBeGreaterThanOrEqual(20)
        ->and($queries)->toBeLessThanOrEqual(3);
});

test('reviews can be submitted', function () {
    $product = makeProduct();

    $this->actingAs(userWithRole())
        ->post(route('products.review', $product), ['product_id' => $product->id, 'rating' => 5])
        ->assertSessionHas('success');

    expect((float) $product->fresh()->rating_avg)->toBe(5.0);
});

test('the search route shows matching products', function () {
    makeProduct(['name_en' => 'Findable', 'name_ar' => 'Findable']);

    $this->get('/product/search?q=Findable')->assertRedirect(route('products.index', ['search' => 'Findable']));
});

test('dashboard categories, tags, and brands can be managed', function () {
    $admin = userWithRole('admin');

    $this->actingAs($admin)->post('/dashboard/categories', ['name_ar' => 'قسم', 'name_en' => 'New Cat', 'status' => 'active'])->assertSessionHasNoErrors();
    expect(Category::where('slug', 'new-cat')->exists())->toBeTrue();

    $this->actingAs($admin)->post('/dashboard/tags', ['name_ar' => 'وسم', 'name_en' => 'New Tag'])->assertSessionHasNoErrors();
    $tag = App\Models\Tag::where('slug', 'new-tag')->firstOrFail();
    $this->actingAs($admin)->put("/dashboard/tags/{$tag->id}", ['name_ar' => 'وسم', 'name_en' => 'Renamed'])->assertSessionHasNoErrors();
    expect($tag->fresh()->slug)->toBe('renamed');

    $brand = App\Models\Brand::create(['name_ar' => 'م', 'name_en' => 'Acme']);
    $this->actingAs($admin)->get("/dashboard/brands/{$brand->id}")->assertOk();
    $this->actingAs($admin)->delete("/dashboard/brands/{$brand->id}")->assertRedirect(route('dashboard.brands.index'));

    $attribute = App\Models\Attribute::create(['name_ar' => 'ل', 'name_en' => 'Color']);
    $value = App\Models\AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Red']);
    $this->actingAs($admin)->put("/dashboard/attribute-values/{$value->id}", ['attribute_id' => $attribute->id, 'value' => 'Blue'])
        ->assertRedirect(route('dashboard.attribute-values.index'));
    expect($value->fresh()->value)->toBe('Blue')
        ->and(App\Models\AttributeValue::count())->toBe(1);
});

test('product managers can delete a variation of their product', function () {
    $product = makeProduct(['product_type' => 'variable']);
    $variation = makeVariation($product, 10);

    $this->actingAs(userWithRole('admin'))->delete(route('dashboard.products.variation.delete', $variation))->assertRedirect();

    expect(ProductVariation::whereKey($variation->id)->exists())->toBeFalse();
});

test('the API requires a token to change data and issues one on login', function () {
    $this->postJson('/api/categories', [])->assertUnauthorized();
    $this->postJson('/api/products', [])->assertUnauthorized();
    $this->getJson('/api/carts')->assertUnauthorized();

    $admin = userWithRole('admin');
    $this->postJson('/api/login', ['email' => $admin->email, 'password' => 'wrong'])->assertStatus(422);
    $token = $this->postJson('/api/login', ['email' => $admin->email, 'password' => 'password'])->assertOk()->json('token');

    $this->withToken($token)->postJson('/api/categories', ['name_ar' => 'ق', 'name_en' => 'Api Cat', 'status' => 'active'])->assertCreated();
    $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('id', $admin->id);

    $product = makeProduct();
    $this->withToken($token)->postJson('/api/carts', ['product_id' => $product->id])->assertCreated()->assertJsonPath('cart.count', 1);
});

test('the API rejects users without permission', function () {
    $token = userWithRole()->createToken('t')->plainTextToken;

    $this->withToken($token)->postJson('/api/categories', ['name_ar' => 'ق', 'name_en' => 'Nope'])->assertForbidden();
});

test('a changed product shows immediately on the dashboard', function () {
    $admin = userWithRole('admin');
    $this->actingAs($admin)->get('/dashboard/products')->assertOk();

    makeProduct(['name_en' => 'JustAdded', 'name_ar' => 'JustAdded']);

    expect($this->actingAs($admin)->get('/dashboard/products')->getContent())->toContain('JustAdded');
});

test('the language switch changes the language', function () {
    $this->get('/lang/en')->assertRedirect();

    expect(session('lang'))->toBe('en');
    $this->get('/')->assertOk()->assertSee('/lang/', false);
});

test('translation groups load and framework aliases exist', function () {
    expect(__('messages.anything'))->toBe('messages.anything')
        ->and(class_exists('Str'))->toBeTrue()
        ->and(class_exists('Currency'))->toBeTrue();
});

test('the product seeder adds ten simple and ten variable products that can be bought', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $this->seed(Database\Seeders\CategorySeeder::class);
    userWithRole('admin');

    $this->seed(Database\Seeders\ProductSeeder::class);
    $this->seed(Database\Seeders\ProductSeeder::class);

    expect(Product::where('product_type', 'simple')->count())->toBe(10)
        ->and(Product::where('product_type', 'variable')->count())->toBe(10);

    Product::all()->each(function (Product $product) {
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee($product->name_en);
        expect(route('products.show', $product->slug))->toContain('/front/product/');
        Illuminate\Support\Facades\Storage::disk('public')->assertExists($product->images->first()->image);
    });

    $customer = userWithRole();
    $simple = Product::where('slug', 'test-wireless-earbuds')->first();
    $this->actingAs($customer)->postJson('/cart', ['product_id' => $simple->id])->assertCreated();

    $variable = Product::where('slug', 'test-men-t-shirt')->first();
    $this->actingAs($customer)
        ->postJson('/cart', ['product_id' => $variable->id, 'variation_id' => $variable->variations->first()->id])
        ->assertCreated();

    $this->actingAs($customer)
        ->postJson('/cart', ['product_id' => Product::where('slug', 'test-silk-scarf')->first()->id])
        ->assertStatus(422);
    $this->actingAs($customer)
        ->postJson('/cart', [
            'product_id' => Product::where('slug', 'test-running-shoes')->first()->id,
            'variation_id' => ProductVariation::where('sku', 'TEST-SHOES-43')->first()->id,
        ])
        ->assertStatus(422);
});

test('the product factory makes valid simple, variable, sale, out-of-stock, and draft products', function () {
    $simple = Product::factory()->create();
    expect($simple->product_type)->toBe(App\Enums\ProductType::SIMPLE)
        ->and($simple->status)->toBe(App\Enums\ProductStatus::Active)
        ->and($simple->in_stock)->toBeTrue()
        ->and($simple->category_id)->not->toBeNull();

    $variable = Product::factory()->variable()->create();
    expect($variable->variations)->toHaveCount(3)
        ->and($variable->variations->where('is_primary', true))->toHaveCount(1)
        ->and($variable->variations->first()->values)->toHaveCount(1)
        ->and($variable->fresh()->in_stock)->toBeTrue();

    expect(Product::factory()->onSale()->create()->discount_percent)->toBe(50)
        ->and(Product::factory()->outOfStock()->create()->in_stock)->toBeFalse()
        ->and(Product::factory()->draft()->create()->status)->toBe(App\Enums\ProductStatus::Draft);

    $this->get(route('products.show', $simple->slug))->assertOk();
    $this->get(route('products.show', $variable->slug))->assertOk();
});

test('the home page shows real deals, categories, and new products', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $deal = Product::factory()->onSale()->create(['name_ar' => 'Great Deal']);
    $soldOutDeal = Product::factory()->onSale()->outOfStock()->create(['name_ar' => 'Gone Deal']);
    $draft = Product::factory()->draft()->create(['name_ar' => 'Hidden Draft']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Great Deal')
        ->assertSee(__('app.save_percent', ['percent' => 50]))
        ->assertDontSee(__('app.save_percent', ['percent' => 0]))
        ->assertSee(__('app.shop_by_category'))
        ->assertDontSee('Hidden Draft')
        ->assertDontSee('dashboard/categories')
        ->assertDontSee('Lorem Ipsum');

    $homeDeals = $this->get('/')->viewData('heroProducts')->pluck('id');
    expect($homeDeals)->toContain($deal->id)->not->toContain($soldOutDeal->id);
});

test('the home page works with an empty store', function () {
    $this->get('/')->assertOk()->assertSee(__('app.welcome_title'));
});

test('a parent category lists the products of its subcategories', function () {
    $parent = makeCategory();
    $child = makeCategory();
    $child->update(['parent_id' => $parent->id]);
    $grandchild = makeCategory();
    $grandchild->update(['parent_id' => $child->id]);

    Product::factory()->create(['name_ar' => 'Deep Product', 'category_id' => $grandchild->id]);
    Product::factory()->create(['name_ar' => 'Other Product', 'category_id' => makeCategory()->id]);

    $this->get(route('products.index', ['category' => $parent->id]))
        ->assertOk()
        ->assertSee('Deep Product')
        ->assertDontSee('Other Product');
});

test('a product without images shows the placeholder', function () {
    expect(Product::factory()->create()->image_url)->toEndWith('assets/images/product-placeholder.svg');
});

function placeCardOrder($test, User $user, Product $product): Order
{
    $test->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertCreated();

    $response = $test->actingAs($user)->post('/checkout', ['payment_method' => 'visa'] + checkoutAddress());
    $order = Order::where('user_id', $user->id)->latest('id')->firstOrFail();

    $response->assertRedirect(route('front.payments.show', $order));

    return $order;
}

test('a test card payment marks the order paid and cannot run twice', function () {
    $user = userWithRole();
    $order = placeCardOrder($this, $user, makeProduct(['stock' => 5]));

    expect($order->payment_method)->toBe(PaymentMethod::VISA)
        ->and($order->payment_status)->toBe(PaymentStatus::PENDING);

    $this->actingAs($user)->get(route('front.payments.show', $order))->assertOk()->assertSee(__('app.test_mode'));

    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'success'])
        ->assertRedirect(route('front.orders.success', $order));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID)
        ->and($order->payments()->sole()->card_last4)->toBe('4242')
        ->and((float) $order->payments()->sole()->amount)->toBe((float) $order->total);

    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'declined'])
        ->assertRedirect(route('front.orders.show', $order));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID)
        ->and($order->payments()->count())->toBe(1);
});

test('a declined card keeps the order and can be retried', function () {
    $user = userWithRole();
    $product = makeProduct(['stock' => 5]);
    $order = placeCardOrder($this, $user, $product);

    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'declined'])
        ->assertRedirect(route('front.payments.show', $order))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::FAILED)
        ->and($product->fresh()->stock)->toBe(4);

    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'success']);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID)
        ->and($order->payments()->count())->toBe(2);
});

test('only the owner can pay, and only with a listed test card', function () {
    $user = userWithRole();
    $order = placeCardOrder($this, $user, makeProduct());

    $this->actingAs(userWithRole())->get(route('front.payments.show', $order))->assertForbidden();
    $this->actingAs(userWithRole())->post(route('front.payments.store', $order), ['card' => 'success'])->assertForbidden();

    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => '4111111111111111'])->assertSessionHasErrors('card');

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});

test('a cash order is not sent to the payment page', function () {
    $user = userWithRole();
    $order = placeOrder($this, $user, makeProduct());

    expect($order->payment_method)->toBe(PaymentMethod::CASH);

    $this->actingAs($user)->get(route('front.payments.show', $order))->assertRedirect(route('front.orders.show', $order));
    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'success']);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING)
        ->and($order->payments()->count())->toBe(0);
});

test('test card payments are unavailable in production', function () {
    $user = userWithRole();
    $order = placeCardOrder($this, $user, makeProduct());
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct()->id])->assertCreated();

    app()->detectEnvironment(fn () => 'production');
    $this->withoutMiddleware(Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

    expect(PaymentMethod::enabled())->toBe([PaymentMethod::CASH]);

    $this->actingAs($user)->post('/checkout', ['payment_method' => 'visa'] + checkoutAddress())->assertSessionHasErrors('payment_method');
    $this->actingAs($user)->get(route('front.payments.show', $order))->assertNotFound();
    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'success'])->assertNotFound();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});

test('an unpaid card order can only be canceled by the admin', function () {
    $admin = userWithRole('admin');
    $product = makeProduct(['stock' => 5]);
    $order = placeCardOrder($this, userWithRole(), $product);

    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $order), ['status' => 'processing'])->assertStatus(422);
    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $order), ['status' => 'canceled'])->assertOk();

    expect($product->fresh()->stock)->toBe(5);
});

test('canceling a paid card order refunds it and returns the stock', function () {
    $admin = userWithRole('admin');
    $user = userWithRole();
    $product = makeProduct(['stock' => 5]);
    $order = placeCardOrder($this, $user, $product);
    $this->actingAs($user)->post(route('front.payments.store', $order), ['card' => 'success']);

    $this->actingAs($admin)->get(route('dashboard.orders.show', $order))->assertOk()->assertSee($order->payments()->sole()->reference);
    $this->actingAs($admin)->putJson(route('dashboard.orders.update', $order), ['status' => 'canceled'])->assertOk();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::REFUNDED)
        ->and($order->payments()->sole()->status)->toBe(PaymentStatus::REFUNDED)
        ->and($product->fresh()->stock)->toBe(5);
});

test('old unpaid orders expire but paid ones are kept', function () {
    $user = userWithRole();
    $paid = placeCardOrder($this, $user, makeProduct());
    $this->actingAs($user)->post(route('front.payments.store', $paid), ['card' => 'success']);
    $unpaid = placeCardOrder($this, $user, makeProduct());

    Order::query()->update(['created_at' => now()->subDays(40)]);

    app()->call([new App\Jobs\DeleteExpiredOrders, 'handle']);

    expect(Order::find($paid->id))->not->toBeNull()
        ->and(Order::find($unpaid->id))->toBeNull();
});

function settingsPayload(array $overrides = []): array
{
    return $overrides + [
        'default_theme' => 'light', 'stagnant_days' => 30, 'dark_mode_enabled' => '1', 'show_social_header' => '1', 'show_social_footer' => '1',
        'currency' => 'USD', 'low_stock_threshold' => 5, 'products_per_page' => 12,
        'show_stock' => '1', 'registration_enabled' => '1', 'product_ratings_visible' => '1',
        'product_reviews_enabled' => '1', 'blog_comments_enabled' => '1',
        
        'site_name' => 'My Store', 'phone' => '012345', 'whatsapp' => '201000000000', 'tax' => 5,
        'facebook' => 'https://facebook.com/mystore', 'twitter' => '', 'instagram' => '',
        'payment_cash_enabled' => '1', 'payment_card_enabled' => '1',
    ];
}

test('only staff with the permission can open site settings', function () {
    $this->get(route('dashboard.settings.edit'))->assertRedirect(route('login'));
    $this->actingAs(userWithRole())->get(route('dashboard.settings.edit'))->assertForbidden();
    $this->actingAs(staffWith())->get(route('dashboard.settings.edit'))->assertForbidden();
    $this->actingAs(staffWith())->put(route('dashboard.settings.update'), settingsPayload())->assertForbidden();

    $this->actingAs(staffWith(['access-dashboard', 'manage-settings']))->get(route('dashboard.settings.edit'))->assertOk();
    $this->actingAs(userWithRole('admin'))->get(route('dashboard.settings.edit'))->assertOk()->assertSee('name="site_name"', false);
});

test('saved settings change the storefront and the tax on new orders', function () {
    $this->actingAs(userWithRole('admin'))
        ->put(route('dashboard.settings.update'), settingsPayload(['whatsapp' => '']))
        ->assertSessionHas('success');

    $this->get('/')
        ->assertSee('<title>My Store</title>', false)
        ->assertSee('012345')
        ->assertSee('https://facebook.com/mystore')
        ->assertDontSee('wa.me')
        ->assertDontSee('1091070473');

    $order = placeOrder($this, userWithRole(), makeProduct());

    expect((float) $order->tax)->toBe(5.0);
});

test('a payment method that is off cannot be used, and one must stay on', function () {
    $admin = userWithRole('admin');
    $this->actingAs($admin)->put(route('dashboard.settings.update'), settingsPayload(['payment_card_enabled' => '0']));

    $user = userWithRole();
    $this->actingAs($user)->postJson('/cart', ['product_id' => makeProduct()->id])->assertCreated();
    $this->actingAs($user)->post('/checkout', ['payment_method' => 'visa'] + checkoutAddress())->assertSessionHasErrors('payment_method');
    $this->actingAs($user)->get('/checkout')->assertOk()->assertDontSee('payment-visa')->assertSee('payment-cash');

    $payload = settingsPayload();
    unset($payload['payment_cash_enabled'], $payload['payment_card_enabled']);
    $this->actingAs($admin)->put(route('dashboard.settings.update'), $payload)->assertSessionHasErrors('payment_cash_enabled');

    expect(PaymentMethod::enabled())->toBe([PaymentMethod::CASH]);
});

test('settings reject bad links and non-image logos, and replacing a logo removes the old file', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $admin = userWithRole('admin');

    $this->actingAs($admin)->put(route('dashboard.settings.update'), settingsPayload(['facebook' => 'javascript:alert(1)']))->assertSessionHasErrors('facebook');
    $this->actingAs($admin)->put(route('dashboard.settings.update'), settingsPayload(['logo' => Illuminate\Http\UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml')]))->assertSessionHasErrors('logo');

    $this->actingAs($admin)->put(route('dashboard.settings.update'), settingsPayload(['logo' => Illuminate\Http\UploadedFile::fake()->image('one.png')]))->assertSessionHas('success');
    $first = App\Models\Setting::get('logo');
    Illuminate\Support\Facades\Storage::disk('public')->assertExists($first);
    $this->get('/')->assertSee('storage/'.$first);

    $this->actingAs($admin)->put(route('dashboard.settings.update'), settingsPayload(['logo' => Illuminate\Http\UploadedFile::fake()->image('two.png')]));
    $second = App\Models\Setting::get('logo');

    expect($second)->not->toBe($first);
    Illuminate\Support\Facades\Storage::disk('public')->assertMissing($first);
    Illuminate\Support\Facades\Storage::disk('public')->assertExists($second);
});

test('the dashboard home counts out of stock simple and variable products', function () {
    Product::factory()->outOfStock()->create();
    Product::factory()->create(['stock' => 50]);
    Product::factory()->create(['stock' => 3]);
    Product::factory()->draft()->outOfStock()->create();
    Product::factory()->variable()->create()->variations()->update(['stock' => 20]);
    Product::factory()->variable()->create()->variations()->update(['stock' => 0]);

    $this->actingAs(userWithRole('admin'))->get('/dashboard')
        ->assertOk()
        ->assertViewHas('productStats', fn (array $stats) => $stats['total'] === 6
            && $stats['active'] === 5
            && $stats['outOfStock'] === 2
            && $stats['lowStock']->pluck('stock_left')->all() === [0, 0, 3]);
});

test('the dashboard home only shows the sections staff may see', function () {
    $this->actingAs(staffWith())->get('/dashboard')
        ->assertOk()
        ->assertViewHas(['orderStats' => null, 'productStats' => null, 'userStats' => null])
        ->assertDontSee(__('app.total_revenue'))
        ->assertDontSee(route('dashboard.orders.index'))
        ->assertDontSee(route('dashboard.users.index'));

    $this->actingAs(staffWith(['access-dashboard', 'view-order']))->get('/dashboard')
        ->assertOk()
        ->assertSee(__('app.total_revenue'))
        ->assertSee(route('dashboard.orders.index'))
        ->assertDontSee(route('dashboard.users.index'))
        ->assertDontSee(__('app.low_stock'));
});

test('the order customer name falls back from the address to the user to guest', function () {
    $order = (new Order)->setRelation('address', null)->setRelation('user', null);
    expect($order->customer_name)->toBe(__('app.guest'));

    $order->setRelation('user', new User(['name' => 'Account Name']));
    expect($order->customer_name)->toBe('Account Name');

    $order->setRelation('address', new OrderAddress(['first_name' => 'Ship', 'last_name' => 'Name']));
    expect($order->customer_name)->toBe('Ship Name');
});

test('revenue counts paid orders and leaves out unpaid and refunded ones', function () {
    $customer = userWithRole();

    foreach ([['processing', 'paid', 100], ['completed', 'paid', 50], ['pending', 'pending', 500], ['refunded', 'refunded', 900]] as [$status, $payment, $total]) {
        Order::create(['user_id' => $customer->id, 'status' => $status, 'payment_status' => $payment, 'payment_method' => 'cash', 'shipping' => 0, 'tax' => 0, 'discount' => 0, 'total' => $total]);
    }

    $this->actingAs(userWithRole('admin'))->get('/dashboard')
        ->assertOk()
        ->assertViewHas('orderStats', fn (array $stats) => (float) $stats['revenue'] === 150.0 && (float) $stats['monthRevenue'] === 150.0);
});

test('admins edit their profile inside the dashboard and customers inside the store', function () {
    $this->actingAs(userWithRole('admin'))->get('/dashboard/profile')->assertOk()->assertViewIs('dashboard.profile.edit');

    $admin = userWithRole('admin');
    $this->actingAs($admin)->patch('/dashboard/profile', ['name' => 'New Name', 'email' => $admin->email])
        ->assertRedirect('/dashboard/profile');
    expect($admin->refresh()->name)->toBe('New Name');

    $this->actingAs(userWithRole())->get('/profile')->assertOk()->assertViewIs('profile.edit');
    $this->actingAs(userWithRole())->get('/dashboard/profile')->assertForbidden();
});

test('every translation key used in the views exists in both languages', function () {
    $keys = collect(Illuminate\Support\Facades\File::allFiles(resource_path('views')))
        ->flatMap(function ($file) {
            preg_match_all("/__\(\s*'app\.([a-zA-Z0-9_]+)'\s*[,)]/", $file->getContents(), $matches);

            return $matches[1];
        })
        ->unique();

    foreach (['en', 'ar'] as $locale) {
        expect($keys->reject(fn ($key) => Illuminate\Support\Facades\Lang::has("app.{$key}", $locale, false))->values()->all())->toBe([], "Missing in {$locale}");
    }
});

test('the bell menu and notifications page list an unread notification and can mark it read', function () {
    $admin = userWithRole('admin');
    $notification = $admin->notifications()->create([
        'id' => (string) Illuminate\Support\Str::uuid(),
        'type' => 'order',
        'data' => ['order_id' => 5, 'message' => 'A new order arrived'],
    ]);

    $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('A new order arrived');

    $this->actingAs($admin)->get('/notifications')
        ->assertOk()
        ->assertSee('A new order arrived')
        ->assertSee(route('notifications.open', $notification->id))
        ->assertSee(route('notifications.markAsRead', $notification->id));

    $this->actingAs($admin)->post(route('notifications.markAsRead', $notification->id))->assertRedirect();
    expect($notification->refresh()->read_at)->not->toBeNull();

    $this->actingAs($admin)->get('/notifications')->assertOk()->assertDontSee(route('notifications.markAsRead', $notification->id));
});

test('the store and dashboard footers credit the developer in both languages', function () {
    foreach (['en', 'ar'] as $locale) {
        $this->withSession(['lang' => $locale]);
        app()->setLocale($locale);

        $this->get('/')->assertOk()->assertSee(__('app.developed_by'))->assertDontSee('GrayGrids');
        $this->actingAs(userWithRole('admin'))->get('/dashboard')->assertOk()->assertSee('Eslam Gaballah');
        auth()->logout();
    }
});

test('views used by the site have no hardcoded Arabic outside the translation files', function () {
    $unused = '#dashboard/(?!products)[a-z_]+/trash|dashboard/(attribute_values|attributes|coupons|governorates|posts)/show|front/comments/#';

    $found = collect(Illuminate\Support\Facades\File::allFiles(resource_path('views')))
        ->reject(fn ($file) => preg_match($unused, $file->getPathname()))
        ->filter(function ($file) {
            $code = preg_replace(['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s', '#/\*.*?\*/#s', '#(^|\s)//.*$#m'], '', $file->getContents());

            return preg_match('/\p{Arabic}/u', str_replace('العربية', '', $code));
        })
        ->map->getRelativePathname()
        ->values()
        ->all();

    expect($found)->toBe([]);
});

test('the translation files have the same keys and no duplicates', function () {
    foreach (['app', 'permission_labels', 'pages'] as $file) {
        $en = include lang_path("en/{$file}.php");
        $ar = include lang_path("ar/{$file}.php");

        expect(array_keys(Illuminate\Support\Arr::dot($en)))->toEqualCanonicalizing(array_keys(Illuminate\Support\Arr::dot($ar)));
    }

    foreach (['en', 'ar'] as $locale) {
        preg_match_all("/^\s*'([a-zA-Z0-9_]+)'\s*=>/m", file_get_contents(lang_path("{$locale}/app.php")), $matches);

        expect(array_keys(array_filter(array_count_values($matches[1]), fn ($count) => $count > 1)))->toBe([]);
    }
});

test('a user can add, replace, and remove a profile picture and set both phone numbers', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $user = User::factory()->create();
    $base = ['name' => $user->name, 'email' => $user->email];

    $this->actingAs($user)->patch('/profile', $base + [
        'phone' => '+20 100 000 0000',
        'secondary_phone' => '01111111111',
        'avatar' => Illuminate\Http\UploadedFile::fake()->image('me.png'),
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();
    $first = $user->avatar;
    expect($user->phone)->toBe('+20 100 000 0000')->and($user->secondary_phone)->toBe('01111111111');
    Illuminate\Support\Facades\Storage::disk('public')->assertExists($first);
    $this->actingAs($user)->get('/profile')->assertSee($user->avatar_url);

    $this->actingAs($user)->patch('/profile', $base + ['avatar' => Illuminate\Http\UploadedFile::fake()->image('new.jpg')])
        ->assertSessionHasNoErrors();
    $second = $user->refresh()->avatar;
    expect($second)->not->toBe($first);
    Illuminate\Support\Facades\Storage::disk('public')->assertMissing($first);

    $this->actingAs($user)->patch('/profile', $base + ['remove_avatar' => '1'])->assertSessionHasNoErrors();
    expect($user->refresh()->avatar)->toBeNull();
    Illuminate\Support\Facades\Storage::disk('public')->assertMissing($second);
});

test('deleting a user removes their profile picture', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $user = User::factory()->create();
    $path = Illuminate\Http\UploadedFile::fake()->image('me.png')->store('avatars', 'public');
    $user->forceFill(['avatar' => $path])->save();

    $user->delete();

    Illuminate\Support\Facades\Storage::disk('public')->assertMissing($path);
});

test('the profile rejects bad pictures and phone numbers', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $user = User::factory()->create();
    $other = User::factory()->create(['phone' => '01222222222']);
    $base = ['name' => $user->name, 'email' => $user->email];

    $svg = Illuminate\Http\UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

    $this->actingAs($user)->patch('/profile', $base + ['avatar' => $svg])->assertSessionHasErrors('avatar');
    $this->actingAs($user)->patch('/profile', $base + ['avatar' => Illuminate\Http\UploadedFile::fake()->image('big.png')->size(3000)])->assertSessionHasErrors('avatar');
    $this->actingAs($user)->patch('/profile', $base + ['phone' => 'not a phone'])->assertSessionHasErrors('phone');
    $this->actingAs($user)->patch('/profile', $base + ['phone' => '01222222222'])->assertSessionHasErrors('phone');
    $this->actingAs($user)->patch('/profile', $base + ['phone' => '01000000000', 'secondary_phone' => '01000000000'])->assertSessionHasErrors('secondary_phone');

    expect($user->refresh()->avatar)->toBeNull();
});

test('the post seeder adds ten published posts with covers and does not repeat them', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $admin = userWithRole('admin');

    $this->seed(Database\Seeders\PostSeeder::class);
    $this->seed(Database\Seeders\PostSeeder::class);

    expect(Post::count())->toBe(10)
        ->and(Post::where('status', 'published')->where('user_id', $admin->id)->count())->toBe(10)
        ->and(Post::has('images')->has('tags')->count())->toBe(10);

    expect(Post::with('images')->get()->every(fn ($post) => $post->images->count() === 1))->toBeTrue();

    $this->get('/posts')->assertOk()->assertSee(Post::latest()->first()->title);
    $this->get('/posts?page=2')->assertOk();
    $this->get(route('front.posts.show', Post::first()))->assertOk()->assertSee('<br', false);
});

test('the footers show the copyright line', function () {
    $this->get('/')->assertOk()->assertSee('Eslam Gaballah')->assertSee(__('app.copyright', ['year' => date('Y')]));
    $this->actingAs(userWithRole('admin'))->get('/dashboard')->assertOk()->assertSee(__('app.copyright', ['year' => date('Y')]));
});

test('the header offers sign in and register to guests and the account menu to users', function () {
    $this->get('/')->assertOk()
        ->assertSee(route('login'))
        ->assertSee(route('register'))
        ->assertDontSee('name="currency_code"', false)
        ->assertDontSee('id="userDropdown"', false);

    $this->actingAs(userWithRole())->get('/')->assertOk()
        ->assertSee('id="userDropdown"', false)
        ->assertSee(route('profile.edit'))
        ->assertDontSee(route('dashboard.index'));

    $this->actingAs(userWithRole('admin'))->get('/')->assertOk()->assertSee(route('dashboard.index'));
});

test('product cards add simple products directly and send variable ones to choose options', function () {
    $simple = Product::factory()->create(['stock' => 5]);
    $variable = Product::factory()->variable()->create();
    $soldOut = Product::factory()->outOfStock()->create();

    $response = $this->get('/')->assertOk();

    $response->assertSee('data-quick-add="'.$simple->id.'"', false)
        ->assertSee('data-quick-add="'.$variable->id.'"', false)
        ->assertDontSee('data-quick-add="'.$soldOut->id.'"', false)
        ->assertSee(__('app.out_of_stock'));

    $this->postJson('/cart', ['product_id' => $simple->id, 'quantity' => 1])
        ->assertCreated()
        ->assertJsonPath('count', 1);
});

test('the home page works with an empty store for guests and shows the post list link only with posts', function () {
    $this->get('/')->assertOk()->assertSee(__('app.welcome_title'))->assertDontSee(__('app.our_latest_news'));
});

function variableProductWithOptions(): array
{
    $product = Product::factory()->create(['product_type' => App\Enums\ProductType::VARIABLE, 'sku' => null, 'price' => null, 'stock' => null]);
    $color = App\Models\Attribute::create(['name_en' => 'Color', 'name_ar' => 'اللون']);
    $size = App\Models\Attribute::create(['name_en' => 'Size', 'name_ar' => 'المقاس']);
    $red = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Red']);
    $blue = App\Models\AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Blue']);
    $small = App\Models\AttributeValue::create(['attribute_id' => $size->id, 'value' => 'S']);
    $large = App\Models\AttributeValue::create(['attribute_id' => $size->id, 'value' => 'L']);

    $soldOutPrimary = $product->variations()->create(['price' => 100, 'compare_price' => 200, 'stock' => 0, 'sku' => 'A1', 'is_primary' => true]);
    $soldOutPrimary->values()->sync([$red->id, $small->id]);
    $inStock = $product->variations()->create(['price' => 150, 'stock' => 7, 'sku' => 'A2']);
    $inStock->values()->sync([$blue->id, $large->id]);

    return compact('product', 'soldOutPrimary', 'inStock', 'red', 'blue', 'small', 'large');
}

test('a variable product card lists its options and starts on a variation that can be bought', function () {
    ['product' => $product, 'inStock' => $inStock, 'soldOutPrimary' => $soldOut, 'red' => $red, 'blue' => $blue] = variableProductWithOptions();

    $options = $product->fresh(['variations.values.attribute', 'variations.images', 'images'])->card_options;

    expect($options['selected']->id)->toBe($inStock->id)
        ->and(collect($options['attributes'])->pluck('values')->flatten(1)->firstWhere('id', $blue->id)['active'])->toBeTrue()
        ->and(collect($options['attributes'])->pluck('values')->flatten(1)->firstWhere('id', $red->id)['available'])->toBeFalse()
        ->and($options['variations']->firstWhere('id', $soldOut->id)['discount'])->toBe(50)
        ->and($options['variations']->firstWhere('id', $inStock->id)['compare'])->toBeNull();

    foreach (['/', '/front/products'] as $page) {
        $this->get($page)->assertOk()
            ->assertSee('data-variation-id="'.$inStock->id.'"', false)
            ->assertSee('data-option-value="'.$red->id.'"', false)
            ->assertSee('data-variations=', false);
    }
});

test('the filtered product list keeps the card options', function () {
    ['blue' => $blue] = variableProductWithOptions();

    $this->get('/front/products', ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSee('data-option-value="'.$blue->id.'"', false);
});

test('the card adds the chosen variation to the cart and respects its stock', function () {
    ['product' => $product, 'inStock' => $inStock, 'soldOutPrimary' => $soldOut] = variableProductWithOptions();

    $this->postJson('/cart', ['product_id' => $product->id])->assertStatus(422);

    $this->postJson('/cart', ['product_id' => $product->id, 'variation_id' => $soldOut->id, 'quantity' => 1])->assertStatus(422);

    $this->postJson('/cart', ['product_id' => $product->id, 'variation_id' => $inStock->id, 'quantity' => 1])
        ->assertCreated()
        ->assertJsonPath('count', 1);

    expect(Cart::first()->variation_id)->toBe($inStock->id);
});

test('a variable product with every variation sold out disables the card button', function () {
    ['product' => $product] = variableProductWithOptions();
    $product->variations()->update(['stock' => 0]);

    $this->get('/')->assertOk()->assertSee(__('app.out_of_stock'))->assertSee('disabled', false);
});

test('the information pages open in both languages and are linked from the footer', function () {
    foreach (['en', 'ar'] as $locale) {
        $this->withSession(['lang' => $locale]);
        app()->setLocale($locale);

        foreach (['about', 'faq', 'privacy', 'terms', 'shipping', 'returns'] as $page) {
            $title = __("pages.{$page}.title");

            $this->get(route("pages.{$page}"))->assertOk()->assertSee($title)->assertDontSee(':site')->assertDontSee(':days');
            $this->get('/')->assertSee(route("pages.{$page}"));
        }

        $this->get(route('contact'))->assertOk()->assertSee(__('app.send_us_message'));
    }
});

test('a visitor can send a contact message and the form rejects bad input', function () {
    $this->post('/contact-us', ['name' => 'Sara', 'email' => 'sara@example.test', 'subject' => 'Hello', 'message' => "A real question\nwith two lines"])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('success');

    expect(App\Models\ContactMessage::where('email', 'sara@example.test')->exists())->toBeTrue();

    $this->post('/contact-us', ['name' => '', 'email' => 'nope', 'subject' => '', 'message' => 'short'])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

    expect(App\Models\ContactMessage::count())->toBe(1);
});

test('a bot that fills the hidden field is told it worked but nothing is saved', function () {
    $this->post('/contact-us', ['website' => 'spam.example', 'name' => 'Bot', 'email' => 'b@example.test', 'subject' => 'Buy', 'message' => 'Buy my product now please'])
        ->assertRedirect(route('contact'));

    expect(App\Models\ContactMessage::count())->toBe(0);
});

test('the contact form limits how often it can be sent', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/contact-us', ['name' => 'a'])->assertRedirect();
    }

    $this->post('/contact-us', ['name' => 'a'])->assertStatus(429);
});

test('staff read and delete contact messages with the permission only', function () {
    $message = App\Models\ContactMessage::create(['name' => 'S', 'email' => 's@example.test', 'subject' => 'Hi', 'message' => '<script>alert(1)</script> hello']);

    $this->actingAs(staffWith())->get('/dashboard/messages')->assertForbidden();
    $this->actingAs(userWithRole())->get("/dashboard/messages/{$message->id}")->assertForbidden();

    $staff = staffWith(['access-dashboard', 'manage-messages']);

    $this->actingAs($staff)->get('/dashboard/messages')->assertOk()->assertSee('Hi')->assertSee('<span class="right badge bg-primary">1</span>', false);

    $this->actingAs($staff)->get("/dashboard/messages/{$message->id}")
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('hello');

    expect($message->refresh()->read_at)->not->toBeNull();

    $this->actingAs($staff)->delete("/dashboard/messages/{$message->id}")->assertRedirect('/dashboard/messages');
    expect(App\Models\ContactMessage::count())->toBe(0);
});

test('unknown addresses show the store 404 page in the visitor language and forbidden pages the 403 page', function () {
    $this->withSession(['lang' => 'ar']);

    $this->get('/this-page-does-not-exist')->assertNotFound()->assertSee('الصفحة غير موجودة')->assertSee(route('home'));

    $this->actingAs(userWithRole())->get('/dashboard')->assertForbidden()->assertSee('غير مسموح بالدخول');

    $this->getJson('/api/not-a-real-endpoint')->assertNotFound()->assertHeader('content-type', 'application/json');
});

test('site settings save the contact details and the contact page shows them', function () {
    $admin = userWithRole('admin');

    $this->actingAs($admin)->put(route('dashboard.settings.update'), [
        'default_theme' => 'light', 'stagnant_days' => 30, 'dark_mode_enabled' => '1', 'show_social_header' => '1', 'show_social_footer' => '1',
        'currency' => 'USD', 'low_stock_threshold' => 5, 'products_per_page' => 12,
        'show_stock' => '1', 'registration_enabled' => '1', 'product_ratings_visible' => '1',
        'product_reviews_enabled' => '1', 'blog_comments_enabled' => '1',
        'site_name' => 'My Store', 'tax' => 0, 'payment_cash_enabled' => '1',
        'contact_email' => 'hello@example.test', 'address' => '1 Nile Street', 'working_hours' => '9 to 5',
    ])->assertSessionHasNoErrors();

    $this->get(route('contact'))->assertOk()->assertSee('hello@example.test')->assertSee('1 Nile Street')->assertSee('9 to 5');
});

/** Saves settings over the defaults, as the settings page does. */
function setSetting(array $values): void
{
    App\Models\Setting::saveValues($values);
}

test('every new setting saves from the settings page', function () {
    $this->actingAs(userWithRole('admin'))->put(route('dashboard.settings.update'), settingsPayload([
        'currency' => 'EGP',
        'show_stock' => null,
        'registration_enabled' => null,
        'product_ratings_visible' => null,
        'product_reviews_enabled' => null,
        'blog_comments_enabled' => null,
        'maintenance_mode' => '1',
        'maintenance_message' => 'Back at noon',
        'low_stock_threshold' => 2,
        'products_per_page' => 6,
        'min_order_amount' => 100,
        'free_shipping_min' => 500,
        'site_description' => 'Best store',
        'announcement' => 'Big sale',
    ]))->assertSessionHasNoErrors();

    $saved = App\Models\Setting::allValues();

    expect($saved['currency'])->toBe('EGP')
        ->and($saved['show_stock'])->toBe('0')
        ->and($saved['registration_enabled'])->toBe('0')
        ->and($saved['product_ratings_visible'])->toBe('0')
        ->and($saved['product_reviews_enabled'])->toBe('0')
        ->and($saved['blog_comments_enabled'])->toBe('0')
        ->and($saved['maintenance_mode'])->toBe('1')
        ->and($saved['maintenance_message'])->toBe('Back at noon')
        ->and((int) $saved['products_per_page'])->toBe(6);
});

test('the settings page rejects a currency it does not know and bad numbers', function () {
    $this->actingAs(userWithRole('admin'))->put(route('dashboard.settings.update'), settingsPayload([
        'currency' => 'XXX', 'products_per_page' => 0, 'low_stock_threshold' => -1, 'min_order_amount' => -5,
    ]))->assertSessionHasErrors(['currency', 'products_per_page', 'low_stock_threshold', 'min_order_amount']);

    expect(App\Models\Setting::get('currency'))->toBe('USD');
});

test('the store currency changes the symbol without changing the number', function () {
    expect(App\Helpers\Currency::format(100))->toContain('100')->toContain('$');

    setSetting(['currency' => 'EUR']);

    expect(App\Helpers\Currency::format(100))->toContain('100')->toContain('€')->not->toContain('$');
});

test('the stock quantity shows only when the setting is on', function () {
    $product = Product::factory()->create(['stock' => 7]);

    $this->get(route('products.show', $product->slug))->assertOk()->assertSee(__('app.in_stock_count', ['count' => 7]));

    setSetting(['show_stock' => '0']);

    $this->get(route('products.show', $product->slug))->assertOk()
        ->assertDontSee(__('app.in_stock_count', ['count' => 7]))
        ->assertSee(__('app.in_stock'));
});

test('variation prices on the product page keep the currency', function () {
    $product = Product::factory()->variable(1)->create();
    $product->variations()->update(['price' => 123]);

    $this->get(route('products.show', $product->slug))->assertOk()
        ->assertSee(json_encode(App\Helpers\Currency::format(123)), false);
});

test('closing product reviews hides the form and blocks new reviews but keeps old ones', function () {
    $product = Product::factory()->create();
    $user = userWithRole();
    App\Models\Review::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 5, 'review' => 'Lovely product']);

    setSetting(['product_reviews_enabled' => '0']);

    $this->actingAs($user)->get(route('products.show', $product->slug))->assertOk()
        ->assertSee('Lovely product')
        ->assertSee(__('app.reviews_closed'))
        ->assertDontSee('id="reviewModal"', false);

    $this->actingAs($user)->post(route('products.review', $product), ['product_id' => $product->id, 'rating' => 1])->assertForbidden();

    expect(App\Models\Review::first()->rating)->toBe(5);
});

test('hiding ratings removes stars and reviews everywhere and blocks new ones', function () {
    $product = Product::factory()->create(['rating_avg' => 4, 'rating_count' => 1]);
    $user = userWithRole();
    App\Models\Review::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 4, 'review' => 'Hidden words']);

    $this->get('/')->assertSee('class="review"', false);

    setSetting(['product_ratings_visible' => '0']);

    $this->get('/')->assertOk()->assertDontSee('class="review"', false);
    $this->get('/front/products')->assertOk()->assertDontSee('class="review"', false)->assertDontSee('value="rating"', false);
    $this->get(route('products.show', $product->slug))->assertOk()->assertDontSee('Hidden words');
    $this->actingAs($user)->post(route('products.review', $product), ['product_id' => $product->id, 'rating' => 1])->assertForbidden();
});

test('closing blog comments hides the forms and blocks new comments but keeps old ones', function () {
    $user = userWithRole();
    $post = Post::forceCreate(['title' => 'T', 'content' => 'C', 'slug' => 't', 'user_id' => $user->id, 'status' => 'published']);
    Comment::create(['post_id' => $post->id, 'user_id' => $user->id, 'body' => 'Old comment']);

    setSetting(['blog_comments_enabled' => '0']);

    $this->actingAs($user)->get(route('front.posts.show', $post))->assertOk()
        ->assertSee('Old comment')
        ->assertSee(__('app.comments_closed'))
        ->assertDontSee('reply-form-', false)
        ->assertDontSee(__('app.post_comment'));

    $this->actingAs($user)->post(route('comments.store', $post->id), ['post_id' => $post->id, 'body' => 'New'])->assertForbidden();

    expect(Comment::count())->toBe(1);
});

test('closing registration hides the links and blocks the form', function () {
    setSetting(['registration_enabled' => '0']);

    $this->get('/')->assertOk()->assertDontSee(route('register'));
    $this->get('/login')->assertOk()->assertDontSee(route('register'));
    $this->get('/register')->assertNotFound();
    $this->post('/register', ['name' => 'a', 'email' => 'new@example.test', 'password' => 'password', 'password_confirmation' => 'password'])->assertNotFound();

    expect(User::where('email', 'new@example.test')->exists())->toBeFalse();
});

test('maintenance mode closes the site to everyone except staff', function () {
    setSetting(['maintenance_mode' => '1', 'maintenance_message' => 'Back at noon']);

    $this->get('/')->assertStatus(503)->assertSee('Back at noon')->assertHeader('Retry-After');
    $this->get('/front/products')->assertStatus(503);
    $this->post('/contact-us', ['name' => 'a'])->assertStatus(503);
    $this->getJson('/api/products')->assertStatus(503)->assertJsonStructure(['message']);

    $this->actingAs(userWithRole())->get('/')->assertStatus(503);

    $this->get('/lang/en')->assertRedirect();

    auth()->logout();
    $this->get('/login')->assertOk();

    $staff = staffWith(['access-dashboard']);
    $this->actingAs($staff)->get('/')->assertOk()->assertSee(__('app.maintenance_banner'));
    $this->actingAs($staff)->get('/dashboard')->assertOk()->assertSee(__('app.maintenance_banner'));
});

test('an admin can sign in during maintenance and reopen the site', function () {
    setSetting(['maintenance_mode' => '1']);

    $admin = userWithRole('admin');
    $admin->forceFill(['password' => Illuminate\Support\Facades\Hash::make('secret-password')])->save();

    $this->post('/login', ['email' => $admin->email, 'password' => 'secret-password'])->assertRedirect();
    $this->assertAuthenticatedAs($admin);

    $this->put(route('dashboard.settings.update'), settingsPayload())->assertSessionHasNoErrors();

    auth()->logout();
    $this->get('/')->assertOk();
});

test('the minimum order amount blocks a small cart at checkout', function () {
    $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
    $customer = userWithRole();
    $this->actingAs($customer)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();

    setSetting(['min_order_amount' => 100]);

    $this->actingAs($customer)->get('/checkout')->assertRedirect();
    $this->actingAs($customer)->post('/checkout', checkoutAddress())->assertRedirect();

    expect(Order::count())->toBe(0);

    setSetting(['min_order_amount' => 50]);

    $this->actingAs($customer)->get('/checkout')->assertOk();
});

test('free shipping applies from the set amount after the discount', function () {
    $this->seed(GovernorateSeeder::class);
    $governorate = Governorate::first();
    $product = Product::factory()->create(['price' => 300, 'stock' => 10]);
    $customer = userWithRole();
    $this->actingAs($customer)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();

    setSetting(['free_shipping_min' => 300]);

    $this->actingAs($customer)->get('/checkout')->assertOk()->assertSee(__('app.free_shipping_over', ['amount' => App\Helpers\Currency::format(300)]));
    $this->actingAs($customer)->post('/checkout', ['governorate_id' => $governorate->id] + checkoutAddress())->assertRedirect();

    expect((float) Order::first()->shipping)->toBe(0.0);
});

test('free shipping does not apply below the set amount', function () {
    $this->seed(GovernorateSeeder::class);
    $governorate = Governorate::first();
    $product = Product::factory()->create(['price' => 299, 'stock' => 10]);
    $customer = userWithRole();
    $this->actingAs($customer)->postJson('/cart', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();

    setSetting(['free_shipping_min' => 300]);

    $this->actingAs($customer)->post('/checkout', ['governorate_id' => $governorate->id] + checkoutAddress())->assertRedirect();

    expect((float) Order::first()->shipping)->toBe((float) $governorate->shipping_price);
});

test('the products per page, low stock alert, description, and announcement follow the settings', function () {
    Product::factory()->count(4)->create(['stock' => 8]);

    setSetting(['products_per_page' => 3, 'low_stock_threshold' => 10, 'site_description' => 'A great store', 'announcement' => 'Big sale today']);

    $this->get('/front/products')->assertOk()->assertViewHas('products', fn ($products) => $products->perPage() === 3);

    $this->get('/')->assertOk()->assertSee('content="A great store"', false)->assertSee('Big sale today');

    $this->actingAs(userWithRole('admin'))->get('/dashboard')->assertOk()
        ->assertViewHas('productStats', fn (array $stats) => $stats['lowStock']->isNotEmpty());
});
