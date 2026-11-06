<a href="{{ route('bundles.show', $bundle) }}" class="bundle-card">
    <div class="bundle-card-images">
        @foreach ($bundle->products->take(4) as $product)
            <img src="{{ $product->image_url }}" alt="" loading="lazy">
        @endforeach
    </div>
    <div class="bundle-card-body">
        <h3>{{ $bundle->name }}</h3>
        <ul class="bundle-tiers">
            @foreach ($bundle->tiers as $tier)
                <li>{{ __('app.bundle_tier_line', ['count' => $tier->min_quantity, 'discount' => $tier->label]) }}</li>
            @endforeach
        </ul>
        <span class="home-link">{{ __('app.bundle_choose_products') }} <i class="lni lni-arrow-right"></i></span>
    </div>
</a>
