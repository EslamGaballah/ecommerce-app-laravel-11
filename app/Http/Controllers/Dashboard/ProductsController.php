<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Products\Attribute;
use App\Models\Products\Product;
use App\Traits\uploadImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
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
        $attributes = Attribute::with('values')->get();
        $category = Category::all();

        $product = new Product();

        // dd($attributes);
        return view('dashboard.products.create',compact('product', 'category', 'attributes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
             'description' => 'nullable|string|max:255',
             'status' => 'in:active,archived,draft',
             'category_id' => 'required|exists:categories,id',
             'price' => 'required|numeric|min:0',
             'compare_price' => 'nullable|numeric|gt:price',
             'image\*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
 
 
         ]);
        $imagePaths = $this->uploadImages($request, 'image');

         $request->merge([
             'slug' => str::slug($request->post('name'))
         ]);
         $data = $request->except('image', 'attributes');

         $product = Product::create($data);

        foreach ($request->attributes as $attributeId => $valueId) {
            DB::table('variant_attribute_value')->insert([
                'id' => $product->id,
                'variant_id' => $attributeId,
                'attribute_value_id' => $valueId,
        ]);
    }



         if ($imagePaths) {
            foreach ($imagePaths as $path) {
                $product->images()->create(['image' => $path]);
            }
        }
        //  dd($request->all());

        return Redirect::route('dashboard.products.index')->with('sucess', 'product created');


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);

        return view('dashboard.products.show', compact('product'));

        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('dashboard.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $slug = $request->merge([
            'slug' => str::slug($request->post('name'))
        ]);
        $product->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // $product = Product::findOrFail($id)->deleteOrFail();
        $product->delete();
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
        $product->restore();
        return Redirect::route('dashboard.products.trash' )
        ->with('success', 'Product restored');
    }
    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();
        return Redirect::route('dashboard.products.trash' )
        ->with('success', 'Product deleted');
    }

}
