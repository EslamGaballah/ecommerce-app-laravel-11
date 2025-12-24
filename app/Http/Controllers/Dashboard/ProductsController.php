<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\UploadImageTrait;
// use App\Traits\uploadImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

class ProductsController extends Controller
{
    use UploadImageTrait ;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();

        $products = Product::with('category','images')
        ->filter($request->query())
        ->paginate(5);

        return view('dashboard.products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        // Gate::authorize('create-product');
         $this->authorize('create', Product::class);

        $category = Category::all();

        $product = new Product();

        return view('dashboard.products.create',compact('product', 'category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $this->authorize('create', Product::class);

    //     // dd($request);
    //     // dd($request->all());
    //    $request->validate([
    //         'name' => 'required|string|max:255',
    //          'description' => 'nullable|string|max:255',
    //          'status' => 'in:active,archived,draft',
    //          'category_id' => 'required|exists:categories,id',
    //          'price' => 'required|numeric|min:0',
    //          'compare_price' => 'nullable|numeric|gt:price',
    //          'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    //      ]);
    //     $imagePaths = $this->uploadImages($request, 'image');

    //      $request->merge([
    //          'slug' => str::slug($request->post('name')),
    //         'user_id'=> auth()->id()
    //      ]);
    //     //  $data['user_id']= auth()->id();
    //      $data = $request->except('image');

    //      $product = Product::create($data);
       
    //      if ($imagePaths) {
    //         foreach ($imagePaths as $path) {
    //             $product->images()->create(['image' => $path]);
    //         }
    //     }
    //     //  dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
             'description' => 'nullable|string|max:255',
             'status' => 'in:active,archived,draft',
             'category_id' => 'required|exists:categories,id',
             'price' => 'required|numeric|min:0',
             'compare_price' => 'nullable|numeric|gt:price',
             'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
         ]);
         DB::beginTransaction();

            try { 
                $product = Product::create([
                    'name' => $request['name'],
                    'category_id' => $request['category_id'],
                    'description' => $request['description'],
                    'slug' => str::slug($request->post('name')),
                    'quantity' => $request ['quantity'],
                    'price' => $request['price'],
                    'compare_price' => $request['compare_price'],
                    'status' => $request['status'],
                    'user_id' => auth()->id()
                ]);
                // create images
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $index => $imageFile) {

                        $path = $this->uploadImage($imageFile, 'products');

                        $image_alt = $request->image_alt[$index] ?? null;

                        // post_images table
                        ProductImage::create([
                            'product_id'    => $product->id,
                            'image' => $path,
                            'image_alt'   => $image_alt,
                        ]);
                    }
                }
                 DB::commit();

             } catch (Throwable $e) {
                    DB::rollBack();
                    throw $e;
                    return back()->with('error', 'Failed to create product');
            }


        return Redirect::route('dashboard.products.index')->with('sucess', 'product created');

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // $product = Product::findOrFail($id);

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
    public function update(Request $request,  Product $product)
    {

        $this->authorize('update', $product);

        // if (! Gate::allows('update-product', $product)) {
        //     abort(403);
        // }

        // $product = Product::findOrFail($id);

        // $product->get();

        $slug = $request->merge([
            'slug' => str::slug($request->post('name'))
        ]);
        $product->update($request->all());

        return Redirect::route('dashboard.products.index')->with('sucess', 'product updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        DB::beginTransaction();

        try {
            // Delete Images
        //     if ($product->images->isNotEmpty()) {
        //         foreach ($product->images as $image) {
        //             $this->deleteImage($image->image);
        //         }
        //     // Delete image from database
        //     // $product->images()->delete();
        // }
        
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

        // $product->forceDelete();

        return Redirect::route('dashboard.products.trash' )
        ->with('success', 'Product deleted');
    }

}
