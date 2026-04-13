<x-front-layout title="Order Details">

<x-slot:breadcrumb>
    <div class="breadcrumbs">
        <div class="container">
            <ul class="breadcrumb-nav">
                <li><a href="{{ route('home') }}">الرئيسية</a></li>
                <li><a href="{{ route('front.orders.index') }}">طلباتي</a></li>
                <li>تفاصيل الطلب</li>
            </ul>
        </div>
    </div>
</x-slot:breadcrumb>

<div class="shopping-cart section">
    <div class="container">

        {{-- HEADER --}}
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4>طلب #{{ $order->id }}</h4>

            <span class="badge bg-{{ $order->status->color() }}">
                {{ $order->status->label() }}
            </span>
        </div>

        {{-- PRODUCTS --}}
        <div class="cart-list-head">

            <div class="cart-list-title">
                <div class="row">
                    <div class="col-lg-2">الصورة</div>
                    <div class="col-lg-3">المنتج</div>
                    <div class="col-lg-2">السعر</div>
                    <div class="col-lg-2">الكمية</div>
                    <div class="col-lg-2">الإجمالي</div>
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
                    <li>Subtotal
                        <span>{{ Currency::format($order->total - $order->shipping - $order->tax + $order->discount) }}</span>
                    </li>
                    <li>Discount
                        <span>- {{ Currency::format($order->discount) }}</span>
                    </li>
                    <li>Shipping
                        <span>{{ Currency::format($order->shipping) }}</span>
                    </li>
                    <li>Tax
                        <span>{{ Currency::format($order->tax) }}</span>
                    </li>
                    <li class="last">Total
                        <span>{{ Currency::format($order->total) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- ADDRESS --}}
        <div class="mt-4">
            <h6>عنوان الشحن</h6>
            <p>{{ $order->address->first_name }} {{ $order->address->last_name }}</p>
            <p>{{ $order->address->phone_number }}</p>
            <p>{{ $order->address->street_address }}<br>
            {{ $order->address->city }}, {{ $order->address->country }}</p>
        </div>

    </div>
</div>

</x-front-layout>