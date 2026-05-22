<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Throwable;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->filter($request->query())
            ->orderByDesc('products_count')
            ->paginate(5);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $data = $request->validated();
            $data['slug'] = \Str::slug($request->post('name'));

            $category = Category::create($data);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        // جلب المنتجات التابعة للقسم مع عمل Paginate تماشياً مع كود الـ Web
        $products = Product::where('category_id', $category->id)->paginate();

        return response()->json([
            'category' => $category,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $data = $request->validated();
            $data['slug'] = \Str::slug($request->post('name'));

            $category->update($data);

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $this->authorize('update', $category);

            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully'
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}