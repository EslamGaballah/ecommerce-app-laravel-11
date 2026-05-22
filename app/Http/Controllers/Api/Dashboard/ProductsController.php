<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Facades\Filters\ProductFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
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
        $filters = new ProductFilter($request);
        $products = $this->productService->getAll($filters);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->create($request);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
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

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $this->authorize('update', $product);

            $updatedProduct = $this->productService->update($request, $product);

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $updatedProduct
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return response()->json([
            'message' => 'Product soft deleted successfully'
        ]);
    }

    /**
     * Display a listing of the trashed resources.
     */
    public function trash()
    {
        $products = Product::onlyTrashed()->paginate();
        
        return response()->json($products);
    }

    /**
     * Restore from trash.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $product);

        $this->productService->restore($product);

        return response()->json([
            'message' => 'Product restored successfully'
        ]);
    }

    /**
     * Remove the specified resource from trash permanently.
     */
    public function forceDelete($id)
    {
        $this->authorize('forceDelete', $id);

        $product = Product::onlyTrashed()
            ->with(['images', 'variations.images'])
            ->findOrFail($id);

        $this->productService->forceDelete($product);

        return response()->json([
            'message' => 'Product deleted permanently'
        ]);
    }
}