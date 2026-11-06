@php
    $options = $product->variations->isNotEmpty() ? $product->card_options : null;
    $selected = $options['selected'] ?? null;

    $price = $selected?->price ?? $product->final_price;
    $compare = $selected?->compare_price ?? $product->final_compare_price;
    $discount = (float) $compare > (float) $price ? (int) round(((float) $compare - (float) $price) / (float) $compare * 100) : 0;
    $image = $selected?->image ? asset('storage/'.$selected->image) : $product->image_url;
    $canBuy = $selected ? $selected->stock > 0 : $product->in_stock;
@endphp

<!-- Start Single Product -->
<div class="single-product" @if ($options) data-variations="{{ $options['variations']->toJson() }}" @endif>
    <div class="product-image">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $image }}"
                 class="img-fluid rounded"
                 alt="{{ $product->name }}"
                 style="aspect-ratio: 1; width: 100%; object-fit: cover;"
                 data-card-image
            >
        </a>

        <span @class(['sale-tag', 'd-none' => ! $discount]) data-card-tag>-{{ $discount }}%</span>

        @auth
            <button type="button" @class(['card-favorite', 'is-active' => in_array($product->id, auth()->user()->favoriteProductIds())])
                data-favorite-toggle="{{ $product->id }}" aria-label="{{ __('app.favorites') }}">
                <i class="lni lni-heart"></i>
            </button>
        @else
            <a href="{{ route('login') }}" class="card-favorite" aria-label="{{ __('app.favorites') }}"><i class="lni lni-heart"></i></a>
        @endauth
    </div>
    <div class="product-info">
        <span class="category">{{ $product->category?->name }}</span>
        <h4 class="title">
            <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
        </h4>

        @if (Setting::get('product_ratings_visible'))
        <ul class="review">
            @for ($i = 1; $i <= 5; $i++)
                <li><i class="lni {{ $i <= round($product->rating_avg) ? 'lni-star-filled' : 'lni-star' }}"></i></li>
            @endfor
            <li><span>({{ $product->rating_count }}) {{ __('app.review') }}</span></li>
        </ul>
        @endif

        @if ($product->description)
            <p class="product-card-excerpt">{{ Str::limit(strip_tags($product->description), 140) }}</p>
        @endif

        <div class="price">
            <span data-card-price>{{ Currency::format($price) }}</span>
            <span @class(['discount-price', 'd-none' => ! $discount]) data-card-compare>{{ $discount ? Currency::format($compare) : '' }}</span>
        </div>

        @if ($options && $options['attributes'])
            <div class="product-options">
                @foreach ($options['attributes'] as $attribute)
                    <div class="product-option-group" role="group" aria-label="{{ $attribute['name'] }}">
                        <span class="product-option-label">{{ $attribute['name'] }}</span>
                        <div class="product-option-values">
                            @foreach ($attribute['values'] as $value)
                                <button type="button"
                                    @class(['product-option', 'is-active' => $value['active'], 'is-unavailable' => ! $value['available']])
                                    aria-pressed="{{ $value['active'] ? 'true' : 'false' }}"
                                    data-option-value="{{ $value['id'] }}">{{ $value['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="button">
            @if ($selected || $canBuy)
                <button type="button" class="btn" data-quick-add="{{ $product->id }}"
                    @if ($selected) data-variation-id="{{ $selected->id }}" @endif
                    @disabled(! $canBuy)>
                    <i class="lni lni-cart"></i>
                    <span data-card-button-label>{{ $canBuy ? __('app.add_to_cart') : __('app.out_of_stock') }}</span>
                </button>
            @else
                <span class="btn btn-secondary disabled">{{ __('app.out_of_stock') }}</span>
            @endif
        </div>
    </div>
</div>
<!-- End Single Product -->
