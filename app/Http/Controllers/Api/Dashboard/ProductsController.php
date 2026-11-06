<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Enums\ProductStatus;
use App\Facades\Filters\ProductFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Public list: active products only.
     */
    public function index(Request $request)
    {
        $products = Product::with('category', 'variations')
            ->active()
            ->filter(new ProductFilter($request))
            ->paginate(15);

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->create($request);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Public detail: other statuses are visible only to users who can view products.
     */
    public function show(Request $request, Product $product)
    {
        if ($product->status !== ProductStatus::Active) {
            abort_unless($request->user('sanctum')?->can('view', $product), 404);
        }

        $product->load([
            'category',
            'variations.values.attribute',
            'variations.images',
            'primaryVariation.images',
        ]);

        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->update($request, $product);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product->fresh(),
        ]);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return response()->json(['message' => 'Product soft deleted successfully']);
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $product);

        $this->productService->restore($product);

        return response()->json(['message' => 'Product restored successfully']);
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()
            ->with(['images', 'variations.images'])
            ->findOrFail($id);

        $this->authorize('forceDelete', $product);

        $this->productService->forceDelete($product);

        return response()->json(['message' => 'Product deleted permanently']);
    }
}
