<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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
        ->paginate(5);

        return view('dashboard.products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Product $product)
    {
        $category = Category::all();

        return view('dashboard.products.create',compact('product', 'category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
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

        return Redirect::route('dashboard.products.index')->with('success', 'product created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('dashboard.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
         $this->authorize('update', $product);

        return view('dashboard.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request,  Product $product)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = str::slug($request->post('name'));

        DB::beginTransaction();

            try { 
                $product->update($data);

                // update images
                if ($request->hasFile('image')) {
                    // delete old Image
                    if ($product->image) {
                       $this->deleteImage($product->image);
                    }
                    
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
                    return back()->with('error', 'Failed to update product');
            }

        return Redirect::route('dashboard.products.index')->with('success', 'product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        DB::beginTransaction();

        try {
        
            $product->delete();
            
            DB::commit();
            
        } catch (Throwable $e) {
            DB::rollBack();
            return Redirect::route('products.index')
                ->with('error', 'Failed to delete product');
        }
            return Redirect::route('dashboard.products.index')
                ->with('sucess', 'Product Deleted');
    }

     public function trash()
    {
        $products = Product::onlyTrashed()->paginate();
        return view('dashboard.products.trash', compact('products'));
    }

     public function restore( $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

         $this->authorize('restore', $product);

        $product->restore();
        return Redirect::route('dashboard.products.trash' )
        ->with('success', 'Product restored');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

         $this->authorize('forcedelete', $product);

          DB::beginTransaction();

        try {
            // Delete Images
            if ($product->images->isNotEmpty()) {
                foreach ($product->images as $image) {
                    $this->deleteImage($image->image);
                }
            // Delete image from database
            $product->images()->delete();
        }
        
        $product->forceDelete();
        
        DB::commit();
        
        } catch (Throwable $e) {
            DB::rollBack();
            return Redirect::route('products.index')
                ->with('error', 'Failed to delete product');
        }

        return Redirect::route('dashboard.products.trash' )
        ->with('success', 'Product deleted');
    }

}
