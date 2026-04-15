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
use App\Services\ProductService;


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
   public function store(StoreProductRequest $request)
    {
        $this->productService->store($request);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'created');
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
        $this->productService->update($product, $request);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'updated');
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
