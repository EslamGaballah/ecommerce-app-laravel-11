<x-front-layout title="Cart">

    <x-slot:breadcrumb>
        <div class="breadcrumbs">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="breadcrumbs-content">
                            <h1 class="page-title">{{ __('app.cart') }}</h1>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <ul class="breadcrumb-nav">
                            <li><a href="{{ route('home') }}"><i class="lni lni-home"></i> {{ __('app.home') }}</a></li>
                            <li><a href="{{ route('products.index') }}">{{ __('app.shop') }}</a></li>
                            <li>{{ __('app.cart') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:breadcrumb>

    <!-- Shopping Cart -->
    <div class="shopping-cart section">
        <div class="container">
            <div class="cart-list-head">
                <!-- Cart List Title -->
                <div class="cart-list-title">
                    <div class="row">
                        <div class="col-lg-2 col-md-1 col-12">
                            <p>{{ __('app.product_image') }}</p>
                        </div>
                        <div class="col-lg-3 col-md-3 col-12">
                            <p>{{ __('app.product_name') }}</p>
                        </div>
                        <div class="col-lg-2 col-md-3 col-12">
                            <p>{{ __('app.price') }}</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>{{ __('app.quantity') }}</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>{{ __('app.subtotal') }}</p>
                        </div>
                        {{-- <div class="col-lg-2 col-md-2 col-12">
                            <p>{{ __('app.discount') }}</p>
                        </div> --}}
                        <div class="col-lg-1 col-md-2 col-12">
                            <p>{{ __('app.remove') }}</p>
                        </div>
                    </div>
                </div>
                <!-- End Cart List Title -->
                @foreach ($cart->items() as $item)
                    @php
                        $price = $item->variation?->price ?? $item->product->price;
                    @endphp
                    <div class="cart-single-list" id="cart-row-{{ $item->id }}">
                        <div class="row align-items-center">
                            <div class="col-lg-2 col-md-1 col-12">
                                <a href="{{ route('products.show', $item->product->slug) }}">
                                    <img src="{{ asset('storage/' . ($item->variation?->image ?? $item->product->images->first()?->image)) }}" alt="{{ $item->product->name }}">
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-3 col-12">
                                <h5 class="product-name"><a href="{{ route('products.show', $item->product->slug) }}">{{ $item->product->name }}</a></h5>
                                <p class="product-des">
                                    @if($item->variation && $item->variation->values->count())
                                        @foreach($item->variation->values as $value)
                                            <span><em>{{ $value->attribute->name }}:</em> {{ $value->value }}</span>
                                        @endforeach
                                    @endif
                                </p>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <div class="count-input">
                                    {{ Currency::format($price) }}
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <div class="count-input">
                                    <input type="number"
                                        class="form-control item-quantity"
                                        data-id="{{ $item->id }}"
                                        value="{{ $item->quantity }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <p class="item-total" id="item-total-{{ $item->id }}">{{ Currency::format($item->quantity * $price) }}</p>
                            </div>
                            <div class="col-lg-1 col-md-2 col-12">
                               <a href="javascript:void(0)" 
                                    class="remove-item"
                                    data-id="{{ $item->id }}">
                                    <i class="lni lni-close"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-12">
                    <!-- Total Amount -->
                    <div class="total-amount">
                        <div class="row">
                            <div class="col-lg-8 col-md-6 col-12">
                                <div class="left">
                                   
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="right">
                                    <ul>
                                        
                                        <li>{{ __('app.subtotal') }}
                                            <span class="cart-subtotal">{{ Currency::format($cart->total()) }}</span>
                                            {{-- <span class="cart-subtotal">{{ Currency::format($totals['original']) }}</span> --}}
                                        </li>
                                        {{-- <li>{{ __('app.shipping') }}<span> تحدد بالعنوان</span></li>
                                        <li>You Save
                                            <span class="cart-save">{{ Currency::format($totals['discount']) }}</span>
                                        </li> --}}
                                        <li class="last">{{ __('app.cart_total')  }}
                                            <span class="cart-pay">{{ Currency::format($cart->total()) }}</span>
                                        </li>
                                    </ul>
                                    <div class="button">
                                        <a href="{{ route('checkout') }}" class="btn">{{ __('app.checkout') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ End Total Amount -->
                </div>
            </div>
        </div>
    </div>
    <!--/ End Shopping Cart -->


</x-front-layout>
