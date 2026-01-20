<!-- Start Single Product -->
<div class="single-product">
    <div class="product-image">
        <img src="{{ $product->image_url }}" alt="#">
        @if ($product->sale_percent)
        <span class="sale-tag">-{{ $product->sale_percent }}%</span>
        @endif
        @if ($product->new)
        <span class="new-tag">{{ __('app.new') }}</span>
        @endif
        
    </div>
    <div class="product-info">
        <span class="category">{{ $product->category->name }}</span>
        <h4 class="title">

            <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
            {{-- <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a> --}}
        </h4>
        
        <ul class="review">
            <span class="avg">
            {{ number_format($product->rating_avg, 1) }}
            </span>
            @for ($i = 1; $i <= 5; $i++)
                <li>
                    <i class="lni lni-star{{ ($i <= $product->rating)? '-filled' : '' }}"></i>
                    {{-- <i class="lni {{ $i <= round($product->rating_avg) ? 'lni-star-filled' : 'lni-star' }}"></i> --}}
                </li>
            @endfor
        </ul>
        <li>
            <span>
                ({{ $product->rating_count }}) {{ __('app.review') }}
            </span>
        </li>

        <div class="price">
            <span>{{ Currency::format($product->price) }}</span>
            @if ($product->compare_price)
                <span class="discount-price">{{ Currency::format($product->compare_price) }}</span>
            @endif
        </div>
        <div class="button">
            <a href="{{ route('products.show', $product->slug) }}" class="btn"><i class="lni lni-cart"></i> {{ __('app.add_to_cart') }}</a>
        </div>
    </div>
</div>
<!-- End Single Product -->