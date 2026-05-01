<div class="cart-items">
    <a href="javascript:void(0)" class="main-btn">
        <i class="lni lni-cart"></i>
        <span class="total-items cart-count-display" id="cart-count">
            {{ $items->count() }}
        </span>
    </a>
    <!-- Shopping Items -->
    <div class="shopping-item">
        <div class="dropdown-cart-header">
            <span><span class="cart-count-display">{{ $items->count() }}</span> {{ __('app.products') }}</span>
            <a href="{{ route('cart.index') }}">{{ __('app.view_cart') }}</a>
        </div>

        <ul class="shopping-list" id="cart-items">
            @foreach($items as $item)
            <li>
                <a href="javascript:void(0)" 
                    class="remove-item remove" 
                    data-id="{{ $item->id }}">
                    <i class="lni lni-close"></i>
                </a>
                <div class="cart-img-head">
                    <a class="cart-img" href="{{ route('products.show', $item->product->slug) }}">
                        <img src="{{ asset('storage/' . ($item->variation?->image ?? $item->product->images->first()?->image)) }}" alt="{{ $item->product->name }}">
                    </a>
                </div>
                <div class="content">
                    <h4><a href="product-details.html">{{ $item->product->name }}</a></h4>
                    @php
                    $price = $item->variation?->price ?? $item->product->price;
                    @endphp

                    <p class="quantity">{{ $item->quantity }}x - <span class="amount">{{ Currency::format($price) }}</span></p>
                </div>
            </li>
            @endforeach
        </ul>

        <div class="bottom">
            <div class="total">
                <span>{{ __('app.total') }}</span>
                <span class="total-amount" id="cart-total">{{ Currency::format($total) }}</span>
            </div>
            <div class="button">
                <a href="{{ route('checkout') }}" class="btn animate">{{ __('app.checkout') }}</a>
            </div>
        </div>
    </div>
    <!--/ End Shopping Items -->
</div>

