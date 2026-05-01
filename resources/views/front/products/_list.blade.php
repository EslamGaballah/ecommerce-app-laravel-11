<div class="row">
    @forelse($products as $product)
        <div class="col-lg-4 col-md-6 col-12">
            {{-- استدعاء الكومبوننت الخاص بك --}}
            <x-product-card :product="$product" />
        </div>
    @empty
        <div class="col-12 text-center">
            <p>{{ __('app.no_products_found') }}</p>
        </div>
    @endforelse
</div>

{{ $products->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}
 

