<?php

namespace App\Services\Bundle;

use App\Models\Bundle;
use Illuminate\Support\Collection;

class BundleService
{
    /**
     * The discount for these cart items. A product counts toward one bundle only: the bundle
     * giving the biggest discount takes its products first, then the next one uses what is left.
     *
     * @return array{discount: float, applied: array<int, array{bundle: Bundle, quantity: int, tier: \App\Models\BundleTier, discount: float}>}
     */
    public function calculate(Collection $items): array
    {
        $lines = $items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => (int) $item->quantity,
            'subtotal' => (float) ($item->variation?->price ?? $item->product?->price) * $item->quantity,
        ]);

        $bundles = $this->runningBundles();
        $applied = [];

        while ($lines->isNotEmpty() && $bundles->isNotEmpty()) {
            $best = $bundles
                ->map(fn ($bundle) => $this->evaluate($bundle, $lines))
                ->filter()
                ->sortByDesc('discount')
                ->first();

            if (! $best || $best['discount'] <= 0) {
                break;
            }

            $applied[] = $best;
            $lines = $lines->reject(fn ($line) => $best['bundle']->products->contains('id', $line['product_id']))->values();
            $bundles = $bundles->reject(fn ($bundle) => $bundle->is($best['bundle']))->values();
        }

        return [
            'discount' => round(array_sum(array_column($applied, 'discount')), 2),
            'applied' => $applied,
        ];
    }

    /**
     * What to tell the customer: bundles with something in the cart, and how far the next tier is.
     *
     * @return array<int, array{bundle: Bundle, quantity: int, tier: ?\App\Models\BundleTier, next: ?\App\Models\BundleTier, missing: int}>
     */
    public function progress(Collection $items): array
    {
        return $this->runningBundles()
            ->map(fn ($bundle) => $this->progressFor($bundle, $items))
            ->filter(fn ($progress) => $progress['quantity'] > 0)
            ->values()
            ->all();
    }

    /** @return array{bundle: Bundle, quantity: int, tier: ?\App\Models\BundleTier, next: ?\App\Models\BundleTier, missing: int} */
    public function progressFor(Bundle $bundle, Collection $items): array
    {
        $quantity = $this->countFor($bundle, $items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => (int) $item->quantity,
        ]));

        $next = $bundle->nextTierAfter($quantity);

        return [
            'bundle' => $bundle,
            'quantity' => $quantity,
            'tier' => $bundle->tierFor($quantity),
            'next' => $next,
            'missing' => $next ? $next->min_quantity - $quantity : 0,
        ];
    }

    private function evaluate(Bundle $bundle, Collection $lines): ?array
    {
        $quantity = $this->countFor($bundle, $lines);
        $tier = $bundle->tierFor($quantity);

        if (! $tier) {
            return null;
        }

        $subtotal = $lines
            ->filter(fn ($line) => $bundle->products->contains('id', $line['product_id']))
            ->sum('subtotal');

        return [
            'bundle' => $bundle,
            'quantity' => $quantity,
            'tier' => $tier,
            'discount' => $tier->discountOn($subtotal),
        ];
    }

    private function countFor(Bundle $bundle, Collection $lines): int
    {
        $matching = $lines->filter(fn ($line) => $bundle->products->contains('id', $line['product_id']));

        return $bundle->distinct_products
            ? $matching->unique('product_id')->count()
            : (int) $matching->sum('quantity');
    }

    private function runningBundles(): Collection
    {
        return Bundle::running()->with(['tiers', 'products:id'])->get();
    }
}
