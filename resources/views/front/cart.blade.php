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
                @foreach ($cart->get() as $item)
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
                                        min="1"
                                        class="form-control"
                                        value="{{ $item->quantity }}"
                                        onchange="updateQuantity('{{ $item->id }}', this.value)">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <p class="item-total" id="item-total-{{ $item->id }}">{{ Currency::format($item->quantity * $price) }}</p>
                            </div>
                            <div class="col-lg-1 col-md-2 col-12">
                                <a class="remove-item" href="javascript:void(0)" onclick="removeItem('{{ $item->id }}')">
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

@push('scripts')
<script>
    // جلب الـ Token بشكل آمن
    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    };

    // ===================================
    // دالة تحديث الكمية (تعمل فور التغيير)
    // ===================================
    function updateQuantity(cartId, quantity) {
        if (parseInt(quantity) < 1) return;

        fetch(`/cart/${cartId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: parseInt(quantity) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // تحديث سعر المنتج الفردي
                document.getElementById(`item-total-${cartId}`).innerText = data.item_total;

                // تحديث إجماليات السلة
                document.querySelector('.cart-subtotal').innerText = data.cart_subtotal;
                document.querySelector('.cart-pay').innerText = data.cart_total;

                // 🔥 تحديث الكومبوننت
                loadCart();
            }
        })
        .catch(err => {
            console.error('Update Error:', err);
            alert('حدث خطأ أثناء التحديث');
        });
    }

    // ===================================
    // دالة حذف المنتج
    // ===================================
    function removeItem(cartId) {
        if (!confirm('هل تريد حذف هذا المنتج؟')) return;

        fetch(`/cart/${cartId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // حذف السطر من الـ HTML مباشرة
                const row = document.getElementById(`cart-row-${cartId}`);
                if (row) row.remove();

                // تحديث إجماليات السلة
                document.querySelector('.cart-subtotal').innerText = data.cart_subtotal;
                document.querySelector('.cart-pay').innerText = data.cart_total;

                // 🔥 تحديث الكومبوننت
                loadCart();
            }
        })
        .catch(err => {
            console.error('Delete Error:', err);
            alert('فشل الحذف، حاول مجدداً');
        });
    }
</script>
@endpush

</x-front-layout>
