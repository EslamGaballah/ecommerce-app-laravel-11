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
// Cart page totals, including the bundle discount
// ================================
function showCartTotals(data) {
    document.querySelectorAll('.cart-subtotal')
        .forEach(el => el.innerText = data.cart_subtotal);

    document.querySelectorAll('.cart-pay')
        .forEach(el => el.innerText = data.cart_total);

    const bundleRow = document.getElementById('cart-bundle-row');

    if (bundleRow) {
        bundleRow.classList.toggle('d-none', !data.bundle_discount);
        bundleRow.querySelector('span').innerText = data.bundle_discount ? `- ${data.bundle_discount}` : '';
    }

    const progress = document.getElementById('cart-bundle-progress');

    if (progress) progress.innerHTML = data.bundle_progress || '';
}

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
        showCartTotals(data);

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
        showCartTotals(data);

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
                                <a class="cart-img" href="/front/product/${item.slug}">
                                    <img src="${item.image}" alt="${item.name}">
                                </a>
                            </div>

                            <div class="content">
                                <h4>
                                    <a href="/front/product/${item.slug}">
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
                html = `<li class="text-center py-3">${window.appText?.cartEmpty ?? ''}</li>`;
            }

            const cartItems = document.getElementById('cart-items');
            if (cartItems) cartItems.innerHTML = html;

            // ✅ تحديث الإجمالي
            const totalEl = document.getElementById('cart-total');
            if (totalEl) totalEl.innerText = data.total;

        })
        .catch(err => console.error('Cart Load Error:', err));
}

// ================================
// Add a simple product from its card
// ================================
function showCartToast(message, isError = false) {
    const toast = document.createElement('div');

    toast.className = 'cart-toast' + (isError ? ' is-error' : '');
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}

document.addEventListener('click', function (e) {
    const button = e.target.closest('[data-quick-add]');

    if (!button) return;

    button.disabled = true;

    fetch('/cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({
            product_id: button.dataset.quickAdd,
            variation_id: button.dataset.variationId || null,
            quantity: 1
        })
    })
    .then(async res => {
        const data = await res.json();

        if (!res.ok) throw new Error(data.message);

        showCartToast(data.message || window.appText?.addedToCart);
        loadCart();
        document.dispatchEvent(new CustomEvent('cart:updated'));
    })
    .catch(err => showCartToast(err.message || window.appText?.somethingWrong, true))
    .finally(() => {
        const card = button.closest('[data-variations]');
        const variation = card && JSON.parse(card.dataset.variations)
            .find(item => String(item.id) === button.dataset.variationId);

        button.disabled = variation ? variation.stock <= 0 : false;
    });
});

// ================================
// Switch variations inside a product card
// These listeners sit on the document, so they also work on cards the product filters load later.
// ================================
function sameValues(variation, values) {
    return variation.values.length === values.length
        && values.every(value => variation.values.includes(value));
}

function chooseVariation(variations, wanted, clicked) {
    const exact = variations.find(variation => sameValues(variation, wanted));

    if (exact) return exact;

    // That combination does not exist: keep the clicked option and as much of the rest as possible.
    const score = variation => wanted.filter(value => variation.values.includes(value)).length;

    return variations
        .filter(variation => variation.values.includes(clicked))
        .sort((a, b) => (b.stock > 0) - (a.stock > 0) || score(b) - score(a))[0];
}

function showVariation(card, variations, variation) {
    const image = card.querySelector('[data-card-image]');
    const tag = card.querySelector('[data-card-tag]');
    const compare = card.querySelector('[data-card-compare]');
    const button = card.querySelector('[data-quick-add]');
    const label = card.querySelector('[data-card-button-label]');
    const inStock = variation.stock > 0;

    if (variation.image) image.src = variation.image;

    card.querySelector('[data-card-price]').textContent = variation.price;

    compare.textContent = variation.compare || '';
    compare.classList.toggle('d-none', !variation.compare);

    tag.textContent = `-${variation.discount}%`;
    tag.classList.toggle('d-none', !variation.discount);

    button.dataset.variationId = variation.id;
    button.disabled = !inStock;
    label.textContent = inStock ? window.appText?.addToCart : window.appText?.outOfStock;

    card.querySelectorAll('.product-option-group').forEach(group => {
        const groupValues = [...group.querySelectorAll('[data-option-value]')].map(option => Number(option.dataset.optionValue));
        const others = variation.values.filter(value => !groupValues.includes(value));

        group.querySelectorAll('[data-option-value]').forEach(option => {
            const value = Number(option.dataset.optionValue);
            const active = variation.values.includes(value);

            option.classList.toggle('is-active', active);
            option.setAttribute('aria-pressed', active ? 'true' : 'false');
            option.classList.toggle('is-unavailable', !variations.some(item => item.stock > 0
                && item.values.includes(value)
                && others.every(other => item.values.includes(other))));
        });
    });
}

document.addEventListener('click', function (e) {
    const option = e.target.closest('[data-option-value]');

    if (!option) return;

    const card = option.closest('[data-variations]');
    const group = option.closest('.product-option-group');
    const variations = JSON.parse(card.dataset.variations);
    const clicked = Number(option.dataset.optionValue);

    const wanted = [...card.querySelectorAll('.product-option-group')].map(item => {
        const active = item === group ? option : item.querySelector('.is-active');

        return active ? Number(active.dataset.optionValue) : null;
    }).filter(value => value !== null);

    const variation = chooseVariation(variations, wanted, clicked);

    if (variation) showVariation(card, variations, variation);
});


// ================================
// Favorites, from any product card
// ================================
document.addEventListener('click', function (e) {
    const button = e.target.closest('[data-favorite-toggle]');

    if (!button) return;

    button.disabled = true;

    fetch(`/favorites/${button.dataset.favoriteToggle}`, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
    })
    .then(res => res.json())
    .then(data => {
        button.classList.toggle('is-active', data.favorited);
        document.querySelectorAll('.favorites-count').forEach(el => el.innerText = data.count);
        showCartToast(data.message);

        // On the favorites page, a removed product leaves the list.
        const item = button.closest('[data-favorite-item]');

        if (item && !data.favorited) {
            item.remove();

            if (!document.querySelector('[data-favorite-item]')) {
                document.getElementById('favorites-list').classList.add('d-none');
                document.getElementById('favorites-empty').classList.remove('d-none');
            }
        }
    })
    .catch(() => showCartToast(window.appText?.somethingWrong, true))
    .finally(() => { button.disabled = false; });
});
