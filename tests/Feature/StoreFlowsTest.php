<?php

use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Coupon;
use App\Models\Governorate;
use App\Models\Order;
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
        'street_address' => 'y', 'country' => 'EG', 'governorate_id' => Governorate::first()->id,
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

test('translation groups load and framework aliases exist', function () {
    expect(__('messages.anything'))->toBe('messages.anything')
        ->and(class_exists('Str'))->toBeTrue()
        ->and(class_exists('Currency'))->toBeTrue();
});
