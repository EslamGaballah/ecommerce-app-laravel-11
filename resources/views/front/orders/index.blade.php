<x-front-layout :title="__('app.my_orders')">

<x-slot:breadcrumb>
    <div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="page-title">{{ __('app.my_orders') }}</h1>
                </div>
                <div class="col-lg-6">
                    <ul class="breadcrumb-nav">
                        <li><a href="{{ route('home') }}">{{ __('app.home') }}</a></li>
                        <li>{{ __('app.my_orders') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-slot:breadcrumb>

<div class="shopping-cart section">
    <div class="container">

        <div class="cart-list-head">

            {{-- Header --}}
            <div class="cart-list-title">
                <div class="row">
                    <div class="col-lg-2">{{ __('app.order_number') }}</div>
                    <div class="col-lg-3">{{ __('app.date') }}</div>
                    <div class="col-lg-2">{{ __('app.status') }}</div>
                    <div class="col-lg-2">{{ __('app.total') }}</div>
                    <div class="col-lg-3">{{ __('app.view') }}</div>
                </div>
            </div>

            {{-- Orders --}}
            @foreach($orders as $order)
                <div class="cart-single-list">
                    <div class="row align-items-center">

                        <div class="col-lg-2">
                            #{{ $order->id }}
                        </div>

                        <div class="col-lg-3">
                            {{ $order->created_at->format('Y-m-d') }}
                        </div>

                        <div class="col-lg-2">
                            <span class="badge bg-{{ $order->status->color() }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <div class="col-lg-2">
                            {{ Currency::format($order->total) }}
                        </div>

                        <div class="col-lg-3">
                            <a href="{{ route('front.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                {{ __('app.view_details') }}
                            </a>
                            @if ($order->isPayable() && ! app()->isProduction())
                                <a href="{{ route('front.payments.show', $order) }}" class="btn btn-sm btn-success">{{ __('app.pay_now') }}</a>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach

        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>

    </div>
</div>

</x-front-layout>