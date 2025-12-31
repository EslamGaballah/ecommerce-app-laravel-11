<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ProductsController extends Controller
{

    use UploadImageTrait ;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with('category','images')
        ->filter($request->query())
        ->orderBy('products.name')
        ->paginate();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = str::slug($request->post('name'));

         DB::beginTransaction();

            try { 
                $product = Product::create($data);

                // create images
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $index => $imageFile) {

                        $path = $this->uploadImage($imageFile, 'products');

                        $image_alt = $request->image_alt[$index] ?? null;

                        // post_images table
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image' => $path,
                            'image_alt' => $image_alt,
                        ]);
                    }
                }

                 DB::commit();

                } catch (Throwable $e) {
                    DB::rollBack();
                    throw $e;
                    return back()->with('error', 'Failed to create product');
                }
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
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = str::slug($request->post('name'));

         $product = Product::findOrFail($id);
         $product->update($data);
         return response()->json($product,201, );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

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
