<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
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
        $category = Category::all();
        $attributes = Attribute::with('attributeValues')->get();

        return view('dashboard.products.create',compact('product', 'category', 'attributes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, Product $product)
    {

        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = str::slug($request->post('name'));
        $data['status'] =  $request->status;
         DB::beginTransaction();

        try 
        { 

            $product = Product::create($data);
            
                // create simple and main Product images
            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $index => $imageFile) {
                    $path = $this->uploadImage($imageFile, 'products');
                    // product_images 
                        $product->images()->create([ // image table with morph
                        'image' => $path,
                        'alt' => $request->image_alt[$index] ?? $product->name
                    ]);
                }
            }

            //  simple Product Stock
            if ($request->product_type  === ProductType::SIMPLE->value) {
                StockMovement::create([
                    'stockable_id'      => $product->id,
                    'stockable_type'    => Product::class,
                    'stock'             => $data['stock'],
                    'type'              => 'in',
                    'reason'            => 'Initial stock',
                    'user_id'           => auth()->id()
                ]);
            } elseif ($request->product_type === ProductType::VARIABLE->value) {
                //  Variable Product
                foreach ($request->variations as $index => $varData) {
                    $sku = blank($varData['sku'])
                        ? SkuGenerator::generateForVariation($product, $varData['attributes'])
                        : $varData['sku'];
                    $variation = $product->variations()->create([
                        // 'product_id'    => $product->id,
                        'price'         => $varData['price'],
                        'compare_price' => $varData['compare_price'] ?? null,
                        'stock'         => $varData['stock'],
                        'sku'           => $sku , 
                        'is_primary'    => $request->primary == $index,
                    ]);

                // attach attribute values
                $variation->values()->sync($varData['attributes']);

                StockMovement::create([
                    'stockable_id'      => $variation->id,
                    'stockable_type'    => ProductVariation::class,
                    'stock'             => $varData['stock'],
                    'type'              => 'in',
                    'reason'            => 'Initial stock',
                    'user_id'           => auth()->id()
                ]);

                // create variation images
                if ($request->hasFile("variations.$index.images")) {
                    foreach ($request->file("variations.$index.images") as $imageFile) {

                        $path = $this->uploadImage($imageFile, 'products');

                        // VARIATION images 
                            $variation->images()->create([ // image table with morph
                            'image' => $path,
                            'alt' => $request->image_alt[$index] ?? $product->name
                        ]);
                    }
                }

            }
        }

        DB::commit();
        return Redirect::route('dashboard.products.index')->with('success', 'product created');

        } catch (Throwable $e) {
                DB::rollBack();
                // Log::error("Product Store Error: " . $e->getMessage());
                return back()->with('error', 'Failed to create product'. $e->getMessage())->withInput();
        }
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

        $attributes = Attribute::with('attributeValues')->get();

        return view('dashboard.products.edit', compact('product', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(UpdateProductRequest $request,  Product $product)
    // {
    //     $data = $request->validated();
    //     $data['user_id'] = auth()->id();
    //     $data['slug'] = str::slug($request->post('name'));

    //     DB::beginTransaction();

    //         try { 
    //             $product->update($data);

    //             // update Image_alt
    //             if ($request->filled('existing_image_alt')) {
    //                 foreach ($request->existing_image_alt as $imageId => $alt) {
    //                     Image::where('id', $imageId)->update([
    //                         'alt' => $alt
    //                     ]);
    //                 }
    //             }

    //             // add images without deleting old images
    //              if ($request->hasFile('image')) {

    //                 foreach ($request->file('image') as $index => $imageFile) {

    //                     $path = $this->uploadImage($imageFile, 'products');

    //                     $product->images()->create([
    //                         'image' => $path,
    //                         'alt'   => $request->image_alt[$index] ?? null,
    //                     ]);
    //                 }
    //             }

    //              DB::commit();

    //          } catch (Throwable $e) {
    //                 DB::rollBack();
    //                 throw $e;
    //                 return back()->with('error', 'Failed to update product');
    //         }

    //     return Redirect::route('dashboard.products.index')->with('success', 'product updated successfully!');
    // }

    public function update(Request $request, Product $product)
    {
        // ابدأ المعاملة المالية (Transaction)
        DB::beginTransaction();

        try {
            // 1. تحديث بيانات المنتج الأساسية
            $product->update([
                'name'        => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status'      => $request->status,
                'slug'        => Str::slug($request->name),
            ]);

            $keepVariationIds = [];

            // 2. معالجة التنوعات (Variations)
            if ($request->has('variations')) {
                foreach ($request->variations as $index => $varData) {
                    
                    // استخدام updateOrCreate لضمان تحديث الموجود أو إنشاء الجديد
                    $variation = $product->variations()->updateOrCreate(
                        ['id' => $varData['id'] ?? null], 
                        [
                            'price'         => $varData['price'],
                            'compare_price' => $varData['compare_price'],
                            'quantity'      => $varData['quantity'],
                            'sku'           => $varData['sku'] ?? $product->id . '-' . $index,
                            'is_primary'    => $request->primary == $index,
                        ]
                    );

                    $keepVariationIds[] = $variation->id;

                    // تحديث السمات (Attributes)
                    $variation->values()->sync($varData['attributes'] ?? []);

                    // معالجة الصور الجديدة
                    if ($request->hasFile("variations.$index.images")) {
                        foreach ($request->file("variations.$index.images") as $imageFile) {
                            $path = $this->uploadImage($imageFile, 'products');
                            $variation->images()->create([
                                'image' => $path,
                                'alt'   => $product->name
                            ]);
                        }
                    }
                }
            }

            // 3. حذف التنوعات التي أزالها المستخدم من الواجهة (Data Consistency)
            $product->variations()->whereNotIn('id', $keepVariationIds)->delete();

            // تأكيد التغييرات في قاعدة البيانات
            DB::commit();

            return Redirect::route('dashboard.products.index')->with('success', 'تم تحديث المنتج وكل تنوعاته بنجاح');

        } catch (\Throwable $e) {
            // في حال حدوث أي خطأ، تراجع عن كل شيء
            DB::rollBack();

            // تسجيل الخطأ للمطور (Log)
            \Log::error("Product Update Failed: " . $e->getMessage());

            return back()->with('error', 'فشلت عملية التحديث، تأكد من البيانات المدخلة.')->withInput();
        }

        return Redirect::route('dashboard.products.index')->with('success', 'Product updated successfully');
        
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

            return back()->with('success', 'تم حذف الـ Variation بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return response()->json(['status' => 'deleted']);

    }
    
}
