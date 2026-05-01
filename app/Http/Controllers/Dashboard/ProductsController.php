<?php

namespace App\Http\Controllers\Dashboard;

use App\Filters\ProductFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

use App\Services\Product\ProductService;

use Illuminate\Http\Request;

use Throwable;

class ProductsController extends Controller
{

    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $filters = new ProductFilter($request);

        $products = $this->productService->getAll($filters);

        return view('dashboard.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $product = new Product();
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = Attribute::with('attributeValues')->get();

        $oldVariations = collect();

        return view('dashboard.products.create',compact('product', 'categories','brands', 'attributes', 'oldVariations'));
    }

     /**
     * Store a newly created resource in storage.
     */
   public function store(StoreProductRequest $request)
    {
        try {
            $this->productService->create($request);

            return redirect()
                ->route('dashboard.products.index')
                ->with('success', 'Product created');

        } catch (\Throwable $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'category',
            'variations.values.attribute',
            'variations.images',
            'primaryVariation.images'
        ]); 

        return view('dashboard.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $product->load([
            'variations.values.attribute',
            'variations.images',
            'images',
            'gallery',
            'thumbnail'
        ]);

        $attributes = Attribute::with('attributeValues')->get();
        $categories = Category::all();
        $brands = Brand::all();
        $oldVariations = $product->variations;
        
        // $thumbnailPath = $images->where('type', 'thumbnail')->first()?->image;
        if ($product->product_type === 'simple') {
            $existingGallery = $product->images
                ->where('type', 'gallery')
                ->map(fn($img) => [
                    'id' => $img->id,
                    'path' => asset('storage/' . $img->image),
                ]);
        } else {
            // variable product → مفيش gallery للمنتج نفسه
            $existingGallery = collect();
        }
      
        return view('dashboard.products.edit', compact(
            'product',
            'attributes',
            'categories',
            'brands',
            'oldVariations',
            'existingGallery'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $this->productService->update($request, $product);

            return redirect()
                ->route('dashboard.products.index')
                ->with('success', 'Product Updated');

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * SOFT DELETE PRODUCTS
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return back()->with('success', 'Deleted');
    }

    /**
     * show trash.
     */
    public function trash()
    {
        $products = Product::onlyTrashed()->paginate();
        return view('dashboard.products.trash', compact('products'));
    }

    /**
     * RESTORE FROM TRASH.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $product);

        $this->productService->restore($product);

        return redirect()
            ->route('dashboard.products.trash')
            ->with('success', 'Product restored');
    }

    /**
     * Remove the specified resource from trash.
     */
   
    public function forceDelete($id)
    {
        $this->authorize('forceDelete', $id);

        $product = Product::onlyTrashed()
            ->with(['images', 'variations.images'])
            ->findOrFail($id);

        $this->productService->forceDelete($product);

        return back()->with('success', 'Deleted permanently');
    }
        
}
