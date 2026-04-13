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
                <a href="javascript:void(0)" onclick="removeItem('{{ $item->id }}')" class="remove" title="Remove this item">
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

@push('scripts')
<script>
    /**
     * تحديث محتويات السلة المصغرة (Mini Cart) والعدادات
     */
    function loadCart() {
        fetch("{{ route('cart.json') }}")
            .then(response => response.json())
            .then(data => {

                // 1. تحديث جميع عدادات السلة في الصفحة (باستخدام الكلاس المشترك)
                document.querySelectorAll('.cart-count-display').forEach(el => {
                    el.innerText = data.count;
                });

                // 2. بناء قائمة المنتجات في السلة المصغرة
                let itemsHtml = '';
                if (data.items.length > 0) {
                    data.items.forEach(item => {
                        itemsHtml += `
                        <li>
                            <a href="javascript:void(0)" onclick="removeItem('${item.id}')" class="remove" title="{{ __('app.remove') }}">
                                <i class="lni lni-close"></i>
                            </a>
                            <div class="cart-img-head">
                                <a class="cart-img" href="/products/${item.slug}">
                                    <img src="${item.image}" alt="${item.name}">
                                </a>
                            </div>
                            <div class="content">
                                <h4><a href="/products/${item.slug}">${item.name}</a></h4>
                                <p class="quantity">${item.quantity}x - <span class="amount">${item.price}</span></p>
                            </div>
                        </li>`;
                    });
                } else {
                    itemsHtml = '<li class="text-center py-3">{{ __('app.cart_empty') }}</li>';
                }

                const cartItemsEl = document.getElementById('cart-items');
                if (cartItemsEl) cartItemsEl.innerHTML = itemsHtml;

                // 3. تحديث الإجمالي الكلي
                const totalEl = document.getElementById('cart-total');
                if (totalEl) totalEl.innerText = data.total;
            })
            .catch(error => console.error('Error loading cart:', error));
    }

    /**
     * حذف منتج من السلة عبر AJAX
     */
    function removeItem(cartId) {
        if (!confirm('{{ __('app.confirm_delete') }}')) return;

        fetch(`/cart/${cartId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // تحديث البيانات في الهيدر والسلة المصغرة
                loadCart();

                // تحديث واجهة صفحة السلة الرئيسية (Cart Page) إذا كان المستخدم فيها
                const row = document.getElementById(`cart-row-${cartId}`);
                if (row) {
                    row.remove();
                    
                    // تحديث ملخص السلة في الصفحة الرئيسية (Subtotal & Total)
                    const subtotalEl = document.querySelector('.cart-subtotal');
                    const payEl = document.querySelector('.cart-pay');
                    
                    if (subtotalEl) subtotalEl.innerText = data.cart_subtotal;
                    if (payEl) payEl.innerText = data.cart_total;
                }
            }
        })
        .catch(error => console.error('Error removing item:', error));
    }

    // تشغيل التحميل عند فتح الصفحة لأول مرة
    document.addEventListener('DOMContentLoaded', function() {
        loadCart();
    });
</script>
@endpush