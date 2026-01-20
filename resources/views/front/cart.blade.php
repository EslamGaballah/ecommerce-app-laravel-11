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
                        <div class="col-lg-1 col-md-3 col-12">
                            <p>{{ __('app.price') }}</p>
                        </div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <p>{{ __('app.quantity') }}</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>{{ __('app.subtotal') }}</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>{{ __('app.discount') }}</p>
                        </div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <p>{{ __('app.remove') }}</p>
                        </div>
                    </div>
                </div>
                <!-- End Cart List Title -->
                @foreach ($cart->get() as $item)
                <!-- Cart Single List list -->
                <div class="cart-single-list" id="{{ $item->id }}">
                    <div class="row align-items-center">
                        <div class="col-lg-2 col-md-1 col-12">
                            <a href="{{ route('products.show', $item->product->slug) }}">
                                <img src="{{ $item->product->image_url }}" alt="#"></a>
                        </div>
                        <div class="col-lg-3 col-md-3 col-12">
                            <h5 class="product-name"><a href="{{ route('products.show', $item->product->slug) }}">
                                    {{ $item->product->name }}</a></h5>
                            <p class="product-des">
                                <span><em>Type:</em> Mirrorless</span>
                                <span><em>Color:</em> Black</span>
                            </p>
                        </div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <div class="count-input">
                                {{ $item->product->price }}
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <div class="count-input">
                                <input type="number"
                                min="1"
                                class="form-control item-quantity" 
                                data-id="{{ $item->id }}" 
                                value="{{ $item->quantity }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p class="item-total">{{ Currency::format($item->quantity * $item->product->price) }}</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>{{ Currency::format(0) }}</p>
                        </div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <a class="remove-item" data-id="{{ $item->id }}" href="javascript:void(0)"><i class="lni lni-close"></i></a>
                        </div>
                    </div>
                </div>
                <!-- End Single List list -->
                @endforeach
            </div>
            <div class="row">
                <div class="col-12">
                    <!-- Total Amount -->
                    <div class="total-amount">
                        <div class="row">
                            <div class="col-lg-8 col-md-6 col-12">
                                <div class="left">
                                    <div class="coupon">
                                        <form action="#" target="_blank">
                                            <input name="Coupon" placeholder="Enter Your Coupon">
                                            <div class="button">
                                                <button class="btn">Apply Coupon</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="right">
                                    <ul>
                                        <li>{{ __('app.cart_subtotal') }}
                                            <span class="cart-subtotal">{{ Currency::format($cart->total()) }}</span>
                                        </li>
                                        <li>{{ __('app.shipping') }}<span>Free</span></li>
                                        <li>You Save<span class="cart-save">{{ Currency::format(0) }}</span>
                                        </li>
                                        <li class="last">You Pay
                                             <span class="cart-pay">{{ Currency::format($cart->total()) }}</span>
                                        </li>
                                    </ul>
                                    <div class="button">
                                        <a href="{{ Route('checkout') }}" class="btn">{{ __('app.checkout') }}</a>
                                        <a href="product-grids.html" class="btn btn-alt">Continue shopping</a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

         const csrf_token = document
        .querySelector('meta[name="csrf-token"]')
        .content;

    
    // Update Quantity
    document.querySelectorAll('.item-quantity').forEach(input => {
        input.addEventListener('change', function () {

            let cartId = this.dataset.id;
            let quantity = parseInt(this.value);

            fetch(`/cart/${cartId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf_token
                },
                body: JSON.stringify({ quantity })
            })
            .then(res => res.json())
            .then(data => {

                if (data.success) {

                    this.closest('.cart-single-list')
                        .querySelector('.item-total').innerText = data.item_total;

                        // update cart totals
                    document.querySelector('.cart-subtotal').innerText = data.cart_total;
                    document.querySelector('.cart-pay').innerText = data.cart_total;
                }
            });
        });
    });

    // حذف المنتج
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function () {
            let cartId = this.dataset.id;

            if (!confirm('هل تريد حذف المنتج؟')) return;

            fetch(`/cart/${cartId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    
                    document.getElementById(cartId).remove();

                    // update totals
                    document.querySelector('.cart-subtotal').innerText = data.cart_total;
                    document.querySelector('.cart-pay').innerText = data.cart_total;

                    // لو السلة فاضية
                    if (!document.querySelector('.cart-single-list')) {
                        document.querySelector('.shopping-cart').innerHTML =
                            '<p class="text-center mt-5">السلة فارغة</p>';
                    }
                }
            });
        });
    });

});


    </script>
    @endpush

</x-front-layout>
