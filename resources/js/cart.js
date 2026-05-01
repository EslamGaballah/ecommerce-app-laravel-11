// ================================
// Helpers
// ================================
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

// ================================
// Init
// ================================
document.addEventListener('DOMContentLoaded', function () {
    loadCart();
});

// ================================
// Update Quantity
// ================================
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('item-quantity')) return;

    const input = e.target;
    const id = input.dataset.id;
    const quantity = parseInt(input.value);

    if (quantity < 1) return;

    fetch(`/cart/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity })
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) return;

        // ✅ تحديث subtotal للمنتج
        const itemTotal = document.getElementById(`item-total-${id}`);
        if (itemTotal) itemTotal.innerText = data.item_total;

        // ✅ تحديث الإجمالي
        document.querySelectorAll('.cart-subtotal')
            .forEach(el => el.innerText = data.cart_subtotal);

        document.querySelectorAll('.cart-pay')
            .forEach(el => el.innerText = data.cart_total);

        // 🔥 تحديث الميني كارت
        loadCart();

    })
    .catch(err => console.error('Update Error:', err));
});

// ================================
// Remove Item
// ================================
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.remove-item');
    if (!btn) return;

    const id = btn.dataset.id;

    fetch(`/cart/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) return;

        // 🔥 تحديث الميني كارت
        loadCart();

        // 🔥 حذف من صفحة الكارت
        const row = document.getElementById(`cart-row-${id}`);
        if (row) row.remove();

        // 🔥 تحديث الإجمالي
        document.querySelectorAll('.cart-subtotal')
            .forEach(el => el.innerText = data.cart_subtotal);

        document.querySelectorAll('.cart-pay')
            .forEach(el => el.innerText = data.cart_total);

    })
    .catch(err => console.error(err));
});

// ================================
// Add To Cart
// ================================
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;

    const productId = btn.dataset.id;
    const quantity = btn.dataset.quantity || 1;

    fetch(`/cart`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) return;

        // 🔥 تحديث الميني كارت
        loadCart();

    })
    .catch(err => console.error('Add Error:', err));
});

// ================================
// Load Mini Cart
// ================================
function loadCart() {

    fetch("/cart/json")
        .then(res => res.json())
        .then(data => {

            // ✅ تحديث العداد
            document.querySelectorAll('.cart-count-display')
                .forEach(el => el.innerText = data.count);

            let html = '';

            if (data.items.length > 0) {

                data.items.forEach(item => {
                    html += `
                        <li>
                            <a href="javascript:void(0)" 
                               class="remove-item remove" 
                               data-id="${item.id}">
                                <i class="lni lni-close"></i>
                            </a>

                            <div class="cart-img-head">
                                <a class="cart-img" href="/products/${item.slug}">
                                    <img src="${item.image}" alt="${item.name}">
                                </a>
                            </div>

                            <div class="content">
                                <h4>
                                    <a href="/products/${item.slug}">
                                        ${item.name}
                                    </a>
                                </h4>

                                <p class="quantity">
                                    ${item.quantity}x - 
                                    <span class="amount">${item.total}</span>
                                </p>
                            </div>
                        </li>
                    `;
                });

            } else {
                html = `<li class="text-center py-3">السلة فارغة</li>`;
            }

            const cartItems = document.getElementById('cart-items');
            if (cartItems) cartItems.innerHTML = html;

            // ✅ تحديث الإجمالي
            const totalEl = document.getElementById('cart-total');
            if (totalEl) totalEl.innerText = data.total;

        })
        .catch(err => console.error('Cart Load Error:', err));
}