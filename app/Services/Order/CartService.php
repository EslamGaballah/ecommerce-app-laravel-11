<?php

namespace App\Services\Order;

use App\Enums\ProductStatus;
use App\Interfaces\CartRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected $cart;

    public function __construct(CartRepositoryInterface $cart)
    {
        $this->cart = $cart;
    }

    public function items(): Collection
    {
        return $this->cart->get();
    }

    public function add(Product $product, int $quantity = 1, $variationId = null)
    {
        if ($product->status !== ProductStatus::Active) {
            throw ValidationException::withMessages(['product_id' => __('app.product_unavailable')]);
        }

        $variation = null;

        if ($variationId) {
            $variation = $product->variations()->whereKey($variationId)->where('is_active', true)->first();

            if (! $variation) {
                throw ValidationException::withMessages(['variation_id' => __('app.product_unavailable')]);
            }
        } elseif ($product->variations()->exists()) {
            throw ValidationException::withMessages(['variation_id' => __('app.choose_options')]);
        }

        $inCart = $this->items()
            ->where('product_id', $product->id)
            ->where('variation_id', $variationId)
            ->sum('quantity');

        $this->ensureInStock($product, $variation, $inCart + $quantity);

        return $this->cart->add($product, $quantity, $variationId);
    }

    public function update($id, int $quantity)
    {
        $item = $this->cart->find($id);

        $this->ensureInStock($item->product, $item->variation, $quantity);

        return $this->cart->update($id, $quantity);
    }

    public function delete($id)
    {
        $this->cart->delete($id);
    }

    public function empty()
    {
        $this->cart->empty();
    }

    public function total(): float
    {
        return $this->cart->total();
    }

    public function count(): int
    {
        return $this->cart->count();
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    private function ensureInStock(Product $product, ?ProductVariation $variation, int $quantity): void
    {
        $stock = $variation?->stock ?? $product->stock;

        if ($quantity > $stock) {
            throw ValidationException::withMessages(['quantity' => __('app.quantity_exceeds_stock')]);
        }
    }
}
