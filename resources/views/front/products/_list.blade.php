{{-- Read by the page script, so the count stays right after filtering. --}}
<span id="products-range" hidden>{{ __('app.showing_range', ['first' => $products->firstItem() ?? 0, 'last' => $products->lastItem() ?? 0, 'total' => $products->total()]) }}</span>

<div class="row">
    @forelse($products as $product)
        <div class="col-lg-4 col-md-6 col-12">
            <x-product-card :product="$product" />
        </div>
    @empty
        <div class="col-12 text-center">
            <p>{{ __('app.no_products_found') }}</p>
        </div>
    @endforelse
</div>

{{ $products->links('pagination::bootstrap-5') }}
