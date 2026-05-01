<?php

namespace App\Services\Order;

use App\Interfaces\CartRepositoryInterface;
use App\Models\Product;
use Illuminate\Support\Collection;

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
        // 🧠 تحديد هل المنتج له variation ولا لا
        if ($product->variations()->exists() && !$variationId) {
            throw new \Exception('يجب اختيار الخصائص');
        }
        
        return $this->cart->add($product, $quantity, $variationId);
    }

    public function update($id, int $quantity)
    {
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
}