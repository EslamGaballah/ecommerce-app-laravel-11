<?php

namespace App\Http\Controllers\Front;

use App\Enums\ProductStatus;
use App\Facades\Filters\ProductFilter;
use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    private const PER_PAGE_CHOICES = [12, 24, 36, 48];


    public function index(Request $request) {

         $filters = new ProductFilter($request);

        $perPageOptions = $this->perPageOptions();
        $perPage = $this->perPage($request, $perPageOptions);

        $products = Product::with('category', 'images', 'variations.images', 'variations.values.attribute', 'primaryVariation.images')
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('front.products._list', compact('products'))->render();
        }

        $categories = Category::withCount('products')->get();
        $brands = Brand::withCount('products')->get();

        return view('front.products.index', compact('products', 'categories', 'brands', 'perPageOptions', 'perPage'));
    }

    /** The store's own number from the settings is always one of the choices. */
    private function perPageOptions(): array
    {
        return collect(self::PER_PAGE_CHOICES)
            ->push((int) Setting::get('products_per_page'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** A visitor's choice is remembered for the session; a number not on the list is ignored. */
    private function perPage(Request $request, array $options): int
    {
        $chosen = (int) $request->query('per_page', $request->session()->get('products_per_page', 0));

        if (! in_array($chosen, $options, true)) {
            return (int) Setting::get('products_per_page');
        }

        $request->session()->put('products_per_page', $chosen);

        return $chosen;
    }

    public function search(Request $request)
    {
        return redirect()->route('products.index', array_filter([
            'search' => $request->input('q', $request->input('search')),
            'category' => $request->input('category'),
        ]));
    }

        public function show(Request $request, Product $product)
    {
        // Staff can preview unpublished products.
        if ($product->status !== ProductStatus::Active) {
            abort_unless(auth()->user()?->can('access-dashboard'), 404);
        }

                $this->countView($request, $product);

        // refresh data to get latest ratings
        $product->refresh();

        $product->load([
            'category',
            'variations.values.attribute',
            'primaryVariation.values.attribute',
            'variations.images',
            'primaryVariation.images',
            'reviews.user'
        ]);

        $defaultVariation = $product->default_variation;

        $attributes = $product->variations
        ->flatMap->values
        ->groupBy('attribute_id')
        ->map(function ($values) {
        return [
            'id'     => $values->first()->attribute->id,
            'name'   => $values->first()->attribute->name,
            'values' => $values->unique('id')
        ];
    });


        $ratingsCount = $product->reviews()
        ->selectRaw('rating, COUNT(*) as count')
        ->groupBy('rating')
        ->pluck('count', 'rating');


        // $comments = $product->comments()->whereNull('parent_id')->with('children')->get();

        return view('front.products.show', [
            ...compact('product', 'defaultVariation', 'attributes', 'ratingsCount'),
            ...$this->sectionsFor($product),
            'productBundles' => $product->bundles()->running()->with('tiers')->get()->filter(fn ($bundle) => $bundle->tiers->isNotEmpty()),
        ]);

    }


        /** Once per visitor session, without touching the product's updated_at. */
    private function countView(Request $request, Product $product): void
    {
        $viewed = $request->session()->get('viewed_products', []);

        if (in_array($product->id, $viewed, true)) {
            return;
        }

        Product::whereKey($product->id)->toBase()->increment('views_count');

        $request->session()->put('viewed_products', [...$viewed, $product->id]);
    }

    /**
     * The rows under the product. A product shown in one row is left out of the next,
     * so the page offers more choice instead of repeating itself.
     */
    private function sectionsFor(Product $product): array
    {
        $suggested = $this->suggestedFor($product);
        $shown = $suggested->pluck('id')->push($product->id);

        $featured = Product::with(BundleController::CARD_RELATIONS)
            ->active()
            ->featured()
            ->whereNotIn('products.id', $shown)
            ->latest()
            ->limit(4)
            ->get();

        $shown = $shown->merge($featured->pluck('id'));

        $bestSellers = Product::with(BundleController::CARD_RELATIONS)
            ->active()
            ->bestSelling()
            ->whereNotIn('products.id', $shown)
            ->limit(4)
            ->get();

        return [
            'suggested' => $suggested,
            'featured' => $featured,
            'bestSellers' => $bestSellers,
            'bundles' => $this->bundlesFor($product),
        ];
    }

    /** Bundles holding this product come first. */
    private function bundlesFor(Product $product)
    {
        return Bundle::running()
            ->with(['tiers', 'products' => fn ($products) => $products->active()->with('images', 'primaryVariation.images', 'variations.images')])
            ->latest()
            ->get()
            ->filter(fn ($bundle) => $bundle->tiers->isNotEmpty() && $bundle->products->isNotEmpty())
            ->sortByDesc(fn ($bundle) => $bundle->products->contains('id', $product->id))
            ->take(3)
            ->values();
    }

    /** The same category first, then the same brand, then the newest, so the row is rarely short. */
    private function suggestedFor(Product $product)
    {
        return Product::with(BundleController::CARD_RELATIONS)
            ->active()
            ->inStock()
            ->whereKeyNot($product->id)
            ->orderByRaw('category_id = ? desc', [$product->category_id])
            ->orderByRaw('brand_id <=> ? desc', [$product->brand_id])
            ->latest()
            ->limit(4)
            ->get();
    }

    public function match(Request $request, Product $product)
    {
       // attributes القادمة من الـ JS (attribute_id => value_id)
    $selected = collect($request->input('attributes', []));

    $variation = $product->variations->first(function ($variation) use ($selected) {

        // attribute_id => value_id الخاصة بالـ variation
        $variationValues = $variation->values
            ->pluck('id', 'attribute_id');

        // كل attributes المختارة لازم تطابق
        return $selected->every(function ($valueId, $attributeId) use ($variationValues) {
            return ($variationValues[$attributeId] ?? null) == $valueId;
        });
    });
        if (!$variation) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists'    => true,
            'id'        => $variation->id,
            'price'     => Currency::format($variation->price),
            'compare_price' => $variation->compare_price
                ? Currency::format($variation->compare_price)
                : null,
            'quantity'  => $variation->stock,
            'images'    => $variation->images->pluck('image')->values(),
            'available' => $variation->stock > 0,
        ]);
    }


}
