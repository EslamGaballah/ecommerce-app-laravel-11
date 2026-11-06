<?php

use App\Models\Attribute;
use App\Models\ContactMessage;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Governorate;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\GovernorateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

test('every GET route works for a guest, a customer, and an admin', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(GovernorateSeeder::class);

    $admin = User::factory()->create();
    $admin->roles()->attach(Role::where('name', 'admin')->first()->id);
    $customer = User::factory()->create();
    $customer->roles()->attach(Role::where('name', 'user')->first()->id);

    $category = Category::forceCreate(['name_ar' => 'c', 'name_en' => 'c', 'slug' => 'c', 'status' => 'active']);
    $brand = Brand::create(['name_ar' => 'b', 'name_en' => 'b']);
    $product = Product::forceCreate([
        'name_ar' => 'p', 'name_en' => 'p', 'slug' => 'p', 'description' => 'd', 'status' => 'active',
        'product_type' => 'variable', 'price' => 10, 'stock' => 5, 'sku' => 'p',
        'category_id' => $category->id, 'brand_id' => $brand->id, 'user_id' => $admin->id,
    ]);
    ProductVariation::forceCreate(['product_id' => $product->id, 'price' => 10, 'stock' => 5, 'sku' => 'v', 'is_primary' => 1, 'is_active' => 1]);
    $attribute = Attribute::create(['name_ar' => 'a', 'name_en' => 'a']);
    $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'v']);
    $tag = Tag::create(['name_ar' => 't', 'name_en' => 't', 'slug' => 't']);
    $post = Post::forceCreate(['title' => 't', 'content' => 'c', 'slug' => 't', 'user_id' => $admin->id, 'status' => 'published']);
    $order = Order::forceCreate(['user_id' => $customer->id, 'number' => '202600001', 'status' => 'pending', 'total' => 1]);
    $order->address()->create(['first_name' => 'a', 'last_name' => 'b', 'email' => 'a@b.test', 'phone_number' => '1', 'city' => 'c', 'street_address' => 's', 'country' => 'EG']);
    $coupon = Coupon::forceCreate(['code' => 'C', 'type' => 'fixed', 'value' => 1]);

    $contactMessage = ContactMessage::create(['name' => 'a', 'email' => 'a@b.test', 'subject' => 's', 'message' => 'a long enough message']);

    $parameters = [
        'message' => $contactMessage->id,
        'product' => $product->id, 'post' => $post->id, 'order' => $order->id, 'coupon' => $coupon->id,
        'category' => $category->id, 'governorate' => Governorate::first()->id, 'role' => Role::first()->id,
        'user' => $customer->id, 'brand' => $brand->id, 'tag' => $tag->id, 'attribute' => $attribute->id,
        'attribute_value' => $value->id, 'lang' => 'en', 'token' => 'x', 'id' => $customer->id, 'hash' => 'x',
    ];

    $failures = [];
    foreach (Route::getRoutes() as $route) {
        if ($route->isFallback || ! in_array('GET', $route->methods()) || str_starts_with($route->uri(), 'sanctum') || str_contains($route->uri(), 'storage/')) {
            continue;
        }

        $uri = preg_replace_callback('/\{(\w+)(:\w+)?\??\}/', function ($match) use ($parameters, $product) {
            return ($match[2] ?? '') === ':slug' ? $product->slug : ($parameters[$match[1]] ?? 1);
        }, $route->uri());

        foreach (['guest' => null, 'customer' => $customer, 'admin' => $admin] as $who => $actor) {
            $this->app['auth']->forgetGuards();
            if ($actor) {
                $this->actingAs($actor);
            }

            $response = $this->get('/'.ltrim($uri, '/'));
            if ($response->status() >= 500) {
                $failures[] = "{$who} GET /{$uri}: ".$response->exception?->getMessage();
            }
        }
    }

    expect($failures)->toBe([]);
});

test('every controller action on a route exists', function () {
    $missing = [];
    foreach (Route::getRoutes() as $route) {
        $action = $route->getActionName();
        if (! str_starts_with($action, 'App\\')) {
            continue;
        }

        [$class, $method] = str_contains($action, '@') ? explode('@', $action) : [$action, '__invoke'];
        if (! method_exists($class, $method)) {
            $missing[] = $action;
        }
    }

    expect($missing)->toBe([]);
});

test('every named route used in views exists', function () {
    $missing = [];
    foreach (File::allFiles(resource_path('views')) as $file) {
        // Trash views are unrouted leftovers, and Blade comments are not rendered.
        if (str_ends_with($file->getFilename(), 'trash.blade.php') && ! str_contains($file->getPath(), 'products')) {
            continue;
        }
        $source = preg_replace('/\{\{--.*?--\}\}/s', '', $file->getContents());
        preg_match_all("/(?<!->)route\(\s*'([^']+)'/", $source, $matches);
        foreach ($matches[1] as $name) {
            if (! Route::has($name)) {
                $missing[] = "{$file->getRelativePathname()}: {$name}";
            }
        }
    }

    expect($missing)->toBe([]);
});

test('no two routes share a name', function () {
    $names = collect(Route::getRoutes())->map(fn ($route) => $route->getName())->filter();

    expect($names->duplicates()->values()->all())->toBe([]);
});
