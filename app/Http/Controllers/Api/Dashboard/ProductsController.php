<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\uploadImages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductsController extends Controller
{

    use uploadImages;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();
        $products = Product::filter($request->query())
        ->orderBy('products.name')
        ->paginate();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        info($request);
        $request->validate([
           'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'in:active,archived,draft',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'


        ]);

        $slug = $request->merge([
            'slug' => str::slug($request->post('name'))
        ]);
        $data = $request->except('image');
        $data['image'] = $this->uploadImages($request);
         

        $product = Product::create($data);
        return response()->json($product,201, );

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $request->validate([
            'name' => 'sometimes|required|string|max:255',
             'description' => 'nullable|string|max:255',
             'status' => 'in:active,archived,draft',
             'category_id' => ' sometimes|required|exists:categories,id',
             'price' => 'sometimes|required|numeric|min:0',
             'compare_price' => 'nullable|numeric|gt:price',
             'quantity' => 'numeric'
 
         ]);

        $request->merge([
             'slug' => str::slug($request->post('name'))
         ]);
         $product = Product::findOrFail($id);
         $product->update($request->all());
         return response()->json($product,201, );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([
            'message' => 'product soft deleted successfully'
        ]);
    }

    public function restore(string $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json([
            'message' => 'product restored successfully'
        ]);
    }

    public function forceDelete(string $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();
        return response()->json([
            'message' => 'product deleted successfully'
        ]);
    }
}
