<x-front-layout :title="__('app.order_details')">

<x-slot:breadcrumb>
    <div class="breadcrumbs">
        <div class="container">
            <ul class="breadcrumb-nav">
                <li><a href="{{ route('home') }}">{{ __('app.home') }}</a></li>
                <li><a href="{{ route('front.orders.index') }}">{{ __('app.my_orders') }}</a></li>
                <li>{{ __('app.order_details') }}</li>
            </ul>
        </div>
    </div>
</x-slot:breadcrumb>

<div class="shopping-cart section">
    <div class="container">

        {{-- HEADER --}}
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4>{{ __('app.order_number') }}: {{ $order->number }}</h4>

            <span class="badge bg-{{ $order->status->color() }}">
                {{ $order->status->label() }}
            </span>
        </div>

        <p class="mb-4">
            {{ __('app.payment_method') }}: {{ $order->payment_method->label() }} —
            <span class="badge bg-{{ $order->payment_status->color() }}">{{ $order->payment_status->label() }}</span>
            @if ($order->isPayable() && ! app()->isProduction())
                <a href="{{ route('front.payments.show', $order) }}" class="btn btn-sm btn-primary ms-2">{{ __('app.pay_now') }}</a>
            @endif
        </p>

        {{-- PRODUCTS --}}
        <div class="cart-list-head">

            <div class="cart-list-title">
                <div class="row">
                    <div class="col-lg-2">{{ __('app.image') }}</div>
                    <div class="col-lg-3">{{ __('app.product') }}</div>
                    <div class="col-lg-2">{{ __('app.price') }}</div>
                    <div class="col-lg-2">{{ __('app.quantity') }}</div>
                    <div class="col-lg-2">{{ __('app.total') }}</div>
                </div>
            </div>

            @foreach($order->items as $item)
                <div class="cart-single-list">
                    <div class="row align-items-center">

                        <div class="col-lg-2">
                            <img src="{{ asset('storage/' . ($item->product->images->first()?->image)) }}">
                        </div>

                        <div class="col-lg-3">
                            <h6>{{ $item->product_name }}</h6>

                            
                        </div>

                        <div class="col-lg-2">
                            {{ Currency::format($item->price) }}
                        </div>

                        <div class="col-lg-2">
                            {{ $item->quantity }}
                        </div>

                        <div class="col-lg-2">
                            {{ Currency::format($item->price * $item->quantity) }}
                        </div>

                    </div>
                </div>
            @endforeach

        </div>

        {{-- TOTAL --}}
        <div class="total-amount mt-4">
            <div class="right">
                <ul>
                    <li>{{ __('app.subtotal') }}
                        <span>{{ Currency::format($order->total - $order->shipping - $order->tax + $order->discount) }}</span>
                    </li>
                    @if ($order->bundle_discount > 0)
                        <li>{{ __('app.bundle_discount') }}
                            <span>- {{ Currency::format($order->bundle_discount) }}</span>
                        </li>
                    @endif
                    <li>{{ $order->bundle_discount > 0 ? __('app.total_discount') : __('app.discount') }}
                        <span>- {{ Currency::format($order->discount) }}</span>
                    </li>
                    <li>{{ __('app.shipping') }}
                        <span>{{ Currency::format($order->shipping) }}</span>
                    </li>
                    <li>{{ __('app.tax') }}
                        <span>{{ Currency::format($order->tax) }}</span>
                    </li>
                    <li class="last">{{ __('app.total') }}
                        <span>{{ Currency::format($order->total) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- ADDRESS --}}
        <div class="mt-4">
            <h6>{{ __('app.shipping_address') }}</h6>
            <p>{{ $order->address->first_name }} {{ $order->address->last_name }}</p>
            <p>{{ $order->address->phone_number }}</p>
            <p>{{ $order->address->street_address }}<br>
            {{ $order->address->city }}, {{ $order->address->country }}</p>
        </div>

    </div>
</div>

</x-front-layout>