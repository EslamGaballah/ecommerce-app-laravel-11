<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Services\SkuGenerator;
use App\Traits\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Throwable;

class ProductsController extends Controller
{
    use UploadImageTrait ;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with('category', 'variations')
        ->filter($request->query())
        ->paginate(5);

        return view('dashboard.products.index',compact('products'));
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

        // $oldVariations = [];
        $oldVariations = collect();

        return view('dashboard.products.create',compact('product', 'categories','brands', 'attributes', 'oldVariations'));
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, Product $product)
    {
// dd($request->all());
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = str::slug($request->post('name_en'));
        $data['status'] =  $request->status;
         DB::beginTransaction();

        try
        {
            $product = Product::create($data);

            if ($request->hasFile('thumbnail')) {
                $thumbnailFile = $request->file('thumbnail');
                $path = $this->uploadImage($thumbnailFile, 'products');
                    $product->images()->create([ // image table with morph
                    'image' => $path,
                    'type' => 'thumbnail',
                    'alt' => $product->name ?? 'product'
                ]);
            }

                // create simple product gallery 
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $index => $imageFile) {
                    $path = $this->uploadImage($imageFile, 'products');
                    // product_images
                        $product->images()->create([ // image table with morph
                        'image' => $path,
                        'type' => 'gallery',

                        'alt' => $product->name  ?? 'product'
                    ]);
                }
            }

            //  simple Product Stock
            if ($request->product_type  === ProductType::SIMPLE->value) {
                $this->trackStockMovement($product, $data['stock']);
            }

            if ($request->product_type === ProductType::VARIABLE->value) {
                // dd($product);
                $this->storeProductWithVariations($request, $product);
            }

            // dd($product);

// dd('ghbgfn');

        DB::commit();
        return Redirect::route('dashboard.products.index')->with('success', 'product created');

        } catch (Throwable $e) {
                DB::rollBack();
                // dd($e->getMessage());
                // Log::error("Product Store Error: " . $e->getMessage());
                return back()->with('error', 'Failed to create product'. $e->getMessage())->withInput();
        }
    }

    public function storeProductWithVariations(StoreProductRequest $request, Product $product)
    {
        //  Variable Product
        foreach ($request->variations as $index => $varData) {
            $sku = blank($varData['sku'])
                ? SkuGenerator::generateForVariation($product, $varData['attribute_value_ids'])
                : $varData['sku'];
            $variation = $product->variations()->create([
                // 'product_id'    => $product->id,
                'price' => $varData['price'],
                'compare_price' => $varData['compare_price'] ?? null,
                'stock' => $varData['stock'],
                'sku' => $sku,
                'is_primary' => $request->primary == $index,
            ]);

            // attach attribute values
            $variation->values()->sync($varData['attribute_value_ids']);

            $this->trackStockMovement($variation, $varData['stock'] ?? 0 );

            // create variation images
           $files = $request->file("variations.$index.images");

            if ($files) {
                foreach ($files as $imageFile) {
                    $path = $this->uploadImage($imageFile, 'products');
                        // VARIATION images
                    $variation->images()->create([ // image table with morph
                        'image' => $path,
                        'type' => 'variation',
                        'alt' => ($product->name ??  'product') . ' - ' . $sku
                    ]);
                }
            }
        }
    }

    public function trackStockMovement($model, $stock, $reason = 'Initial stock'){

        StockMovement::create([
            'stockable_id' => $model->id,
            'stockable_type' => $model->getMorphClass(), // ProductVariation::class,
            'stock' => $stock,
            'type' => 'in',
            'reason' => $reason,
            'user_id' => auth()->id()
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

        $product->load(['category', 'variations.values.attribute', 'variations.images','primaryVariation.images']); 

        return view('dashboard.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
         $this->authorize('update', $product);

         $product->load(['variations.values.attribute', 'variations.images', 'images', 'gallery', 'thumbnail' ]);

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
        //     ->with(['values', 'images'])
        //     ->get()
        //     ->map(function ($variation) {
        //         return [
        //             'id' => $variation->id,
        //             'sku' => $variation->sku,
        //             'price' => $variation->price,
        //             'compare_price' => $variation->compare_price,
        //             'stock' => $variation->stock,
        //             'values' => $variation->values,
        //             'images' => $variation->images,
        //         ];
        // });
        // dd($product->thumbnail);
// dd($product->images);
        return view('dashboard.products.edit', compact('product', 'attributes', 'categories', 'brands',
        'oldVariations',
        'existingGallery'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($request->post('name_en'));
        $data['status'] = $request->status;

        DB::beginTransaction();

        try {

            // update product
            $product->update($data);

            /*
            |--------------------------------------------------------------------------
            | Product Images
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('thumbnail')) {
            // حذف الصورة القديمة
            if ($oldThumbnail = $product->images()->where('type', 'thumbnail')->first()) {
                $this->deleteImage($oldThumbnail->image);
                $oldThumbnail->delete();
            }
            
            // رفع الصورة الجديدة
            $thumbnailFile = $request->file('thumbnail');
            $path = $this->uploadImage($thumbnailFile, 'products');
            $product->images()->create([
                'image' => $path,
                'type' => 'thumbnail',
                'alt' => $product->name
            ]);
        }

            if ($request->filled('deleted_images')) {
                $deletedIds = is_array($request->deleted_images) ? $request->deleted_images : explode(',', $request->deleted_images);
                $images = Image::whereIn('id', $deletedIds)->get();
                foreach ($images as $img) {
                    $img->delete();
                }
            }
            if ($request->hasFile('gallery')) {

                foreach ($request->file('gallery') as $index => $imageFile) {

                    $path = $this->uploadImage($imageFile, 'products');

                    $product->images()->create([
                        'image' => $path,
                        'type' => 'gallery',
                        'alt' => $product->name ?? 'product'
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Simple Product Stock
            |--------------------------------------------------------------------------
            */
            if ($request->product_type === ProductType::SIMPLE->value) {

                if ($product->stock != $data['stock']) {
                    $this->trackStockMovement($product, $data['stock'], 'Stock updated');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Variable Product
            |--------------------------------------------------------------------------
            */
            if ($request->product_type === ProductType::VARIABLE->value) {

                $this->updateProductVariations($request, $product);
            } else {
                $product->variations()->delete();
                $this->trackStockMovement($product, $data['stock'], 'Stock updated');
            }

            DB::commit();

            return Redirect::route('dashboard.products.index')
                ->with('success', 'Product Updated Successfully');

        } catch (Throwable $e) {

            DB::rollBack();

            return back()
                ->with('error', 'Failed to update product ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateProductVariations(updateProductRequest $request, Product $product)
    {

        $existingVariationIds = $product->variations()->pluck('id')->toArray();

        $incomingIds = [];

        foreach ($request->variations as $index => $varData) {

            $sku = blank($varData['sku'])
                ? SkuGenerator::generateForVariation($product, $varData['attribute_value_ids'])
                : $varData['sku'];

            /*
            |--------------------------------------------------------------------------
            | Update Or Create Variation
            |--------------------------------------------------------------------------
            */
            if (!empty($varData['id'])) {

                $variation = $product->variations()->find($varData['id']);

                $variation->update([
                    'price' => $varData['price'],
                    'compare_price' => $varData['compare_price'] ?? null,
                    'stock' => $varData['stock'],
                    'sku' => $sku,
                    'is_primary' => $request->primary == $index,
                ]);

                $incomingIds[] = $variation->id;

            } else {

                $variation = $product->variations()->create([
                    'price' => $varData['price'],
                    'compare_price' => $varData['compare_price'] ?? null,
                    'stock' => $varData['stock'],
                    'sku' => $sku,
                    'is_primary' => $request->primary == $index,
                ]);

                $incomingIds[] = $variation->id;

                $this->trackStockMovement($variation, $varData['stock'] ?? 0);
            }

            /*
            |--------------------------------------------------------------------------
            | Sync Attribute Values
            |--------------------------------------------------------------------------
            */

            $variation->values()->sync($varData['attribute_value_ids']);

            /*
            |--------------------------------------------------------------------------
            | Variation Images
            |--------------------------------------------------------------------------
            */
            $files = $request->file("variations.$index.images");

            if ($files) {
                // delete old images
                /*
                if ($variation->images->isNotEmpty()) {
                    foreach ($variation->images as $img) {
                        $this->deleteImage($img->image);
                    }
                    $variation->images()->delete();
                }
                */

                foreach ($files as $imageFile) {
                    $path = $this->uploadImage($imageFile, 'products');
                    $variation->images()->create([
                        'image' => $path,
                        'alt' => $product->name ?? 'product'
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Removed Variations
        |--------------------------------------------------------------------------
        */

       $deletedIds = array_diff($existingVariationIds, $incomingIds);
    if (!empty($deletedIds)) {
        $variationsToDelete = ProductVariation::with('images')->whereIn('id', $deletedIds)->get();

        foreach ($variationsToDelete as $vToDelete) {
            foreach ($vToDelete->images as $img) {
                $this->deleteImage($img->image);
            }
            $vToDelete->images()->delete();
            $vToDelete->values()->detach();
            $vToDelete->delete();
        }
    }
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

    /**
     * show trash.
     */
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

    /**
     * Remove the specified resource from trash.
     */
    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->with(['images', 'variations.images'])->findOrFail($id);
        $this->authorize('forcedelete', $product);

        DB::beginTransaction();

        try {
            // 1. حذف صور المنتج الأساسي (الـ Thumbnail والـ Gallery)
            foreach ($product->images as $image) {
                $this->deleteImage($image->image);
            }
            $product->images()->delete();

            // 2. المرور على الـ Variations وحذف صورها
            foreach ($product->variations as $variation) {
                foreach ($variation->images as $vImage) {
                    $this->deleteImage($vImage->image);
                }
                // حذف سجلات صور الـ variation
                $variation->images()->delete();
                // فصل السمات في الجدول الوسيط
                $variation->values()->detach();
            }
            
            // حذف الـ variations من قاعدة البيانات
            $product->variations()->delete();

            // 3. الحذف النهائي للمنتج
            $product->forceDelete();

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            return Redirect::route('dashboard.products.trash') // تم تعديل الراوت هنا ليكون أدق
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }

        return Redirect::route('dashboard.products.trash')
            ->with('success', 'Product and all its assets deleted permanently');
    }
    /**
     * Remove variation.
     */
    public function deleteVariation( ProductVariation $variation) 
    {
        // تحميل العلاقة مسبقاً للتأكد من وجود البيانات وتجنب الخطأ
        $variation->load('product'); 

        if (!$variation->product) {

            return back()->with('error', 'المنتج المرتبط بهذا التنوع غير موجود');
           
        }
      
        DB::beginTransaction();

        try {
            // last Variation
            if ($variation->product->variations()->count() === 1) {
                return back()->with('error', 'لا يمكن حذف آخر Variation للمنتج');
            }

            // primary variation
            if ($variation->is_primary) {
                $next = $variation->product
                    ->variations()
                    ->where('id', '!=', $variation->id)
                    ->first();

                if ($next) {
                    $next->update(['is_primary' => true]);
                }
            }

            // delete storage images 
            if ($variation->images->isNotEmpty()) {
                foreach ($variation->images as $image) {
                
                $this->deleteImage($image->image);
                }

                $variation->images()->delete();

            }
           
            // 🔗 فصل السمات
            $variation->values()->detach();

            // 🗑️ حذف الـ variation
            $variation->delete();

            DB::commit();

            // return back()->with('success', 'تم حذف الـ Variation بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return response()->json(['status' => 'deleted']);

    }
    
}
