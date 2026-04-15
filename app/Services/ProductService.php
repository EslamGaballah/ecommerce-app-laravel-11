<?php

namespace App\Services;

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

    public function create(array $data, $request): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->productRepo->create($data);

            // 🖼️ الصور
            $this->imageService->handleCreateImages($product, $request);

            // 📦 stock
            if ($data['product_type'] === 'simple') {
                $this->stockService->createStock($product, $data['stock']);
            }

            // 🔀 variations
            if ($data['product_type'] === 'variable') {
                $this->variationService->createVariations($product, $request);
            }

            DB::commit();
            return $product;

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}