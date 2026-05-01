<?php

namespace App\Services\Product;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Filters\ProductFilter;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepo,
        protected ProductImageService $imageService,
        protected ProductVariationService $variationService,
        protected StockService $stockService,
    ) {}

    public function getAll (ProductFilter $filters)
    {
       $key = 'products_' . md5(json_encode(request()->query()));  // mdf يستخدم فقط عند وجود بيانات معقدة (array / query / filters)

    //    $key = 'products_' . json_encode($filters);

        return cache()->remember($key, 60, function () use ($filters) {
            return $this->productRepo->getAll($filters);
        });
    }

    public function create($request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['slug'] = str()->slug($request->name_en);

            $product = $this->productRepo->create($data);

            // images
            $this->imageService->handleProductImages($request, $product);

            // stock
            if ($request->product_type === 'simple') {
                $this->stockService->create($product, $data['stock']);
            }

            // variations
            if ($request->product_type === 'variable') {
                $this->variationService->store($request, $product);
            }

            DB::commit();

            event(new ProductCreated($product));

            return $product;

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($request, Product $product)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['slug'] = str()->slug($request->name_en);

            $product->update($data);

            // images
            $this->imageService->updateProductImages($request, $product);

            /*
            |--------------------------------------------------------------------------
            | STOCK
            |--------------------------------------------------------------------------
            */
            if ($request->product_type === 'simple') {

                if ($product->stock != $data['stock']) {
                    $this->stockService->create($product, $data['stock'], 'Stock updated');
                }

                // delete variations if exists before
                $this->variationService->deleteAll($product);
            }

            /*
            |--------------------------------------------------------------------------
            | VARIATIONS
            |--------------------------------------------------------------------------
            */
            if ($request->product_type === 'variable') {
                $this->variationService->update($request, $product);
            }

            DB::commit();

            event(new ProductUpdated($product));

            return true;

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SOFT DELETE
    |--------------------------------------------------------------------------
    */
    public function delete(Product $product)
    {
        return $product->delete();
    }

     /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */
    public function restore(Product $product)
    {
        return $product->restore();
    }

     /*
    |--------------------------------------------------------------------------
    | FORCE DELETE
    |--------------------------------------------------------------------------
    */
    public function forceDelete(Product $product)
    {
        DB::beginTransaction();

        try {
            // 1. delete product images
            $this->imageService->deleteProductImages($product);

            // 2. delete variations بالكامل
            $this->variationService->forceDeleteAll($product);

            // 3. delete product
            $product->forceDelete();

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}