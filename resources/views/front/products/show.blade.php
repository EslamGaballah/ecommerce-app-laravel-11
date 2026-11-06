<x-front-layout :title="$product->name">

    <x-slot:breadcrumb>
        <div class="breadcrumbs">
            <div class="container">
                <div class="row align-items-center">

                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="breadcrumbs-content">
                            <h1 class="page-title">{{ $product->name }}</h1>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-12">
                        <ul class="breadcrumb-nav">
                            <li><a href="{{ route('home') }}"><i class="lni lni-home"></i> {{ __('app.home') }}</a></li>
                            <li><a href="{{ route('products.index') }}">{{ __('app.shop') }}</a></li>
                            <li>{{ $product->name }}</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </x-slot:breadcrumb>


    <section class="item-details section">

        <div class="container">

            <div class="top-area">

                <div class="row align-items-center">

                    {{-- الصور --}}
                    <div class="col-lg-6 col-md-12 col-12">

                        <div class="product-images">

                            <main id="gallery">

                                <div class="main-img">
                                    <img id="current"
                                         src="{{ asset('storage/' . (
                                            optional($defaultVariation)->image
                                            ?? $product->image
                                            ?? $product->images->first()?->image
                                        )) }}"
                                         alt="{{ $product->name }}">
                                </div>

                                <div class="images">

                                    {{-- صور الفارييشن --}}
                                    @if($defaultVariation && $defaultVariation->images->count())

                                        @foreach($defaultVariation->images as $image)

                                            <img
                                                src="{{ asset('storage/'.$image->image) }}"
                                                class="img"
                                                onclick="document.getElementById('current').src=this.src">

                                        @endforeach

                                        {{-- صور المنتج البسيط --}}
                                    @elseif($product->images && $product->images->count())

                                        @foreach($product->images as $image)

                                            <img
                                                src="{{ asset('storage/'.$image->image) }}"
                                                class="img"
                                                onclick="document.getElementById('current').src=this.src">

                                        @endforeach

                                    @endif

                                </div>

                            </main>

                        </div>

                    </div>


                    {{-- معلومات المنتج --}}
                    <div class="col-lg-6 col-md-12 col-12">

                        <div class="product-info">

                            <h2 class="title">{{ $product->name }}</h2>

                            <p class="category">
                                <i class="lni lni-tag"></i>
                                {{ __('app.category') }} :
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </p>


                            <h3 class="price" id="product-price">

                                {{ Currency::format($product->final_price) }}

                                @if($product->final_compare_price)

                                    <span class="discount-price text-muted text-decoration-line-through">
                                        {{ Currency::format($product->final_compare_price) }}
                                    </span>

                                @endif

                            </h3>


                            @if(!$product->variations->count())

                                <p class="mt-2 {{ $product->stock > 0 ? 'text-success':'text-danger' }}">

                                    {{ $product->stock > 0 ? (Setting::get('show_stock') ? __('app.in_stock_count', ['count' => $product->stock]) : __('app.in_stock')) : __('app.out_of_stock') }}

                                </p>

                            @endif


                            <p class="info-text">{{ $product->description }}</p>

                            <p id="stock-status"></p>


                            @foreach ($productBundles as $bundle)
                                <a href="{{ route('bundles.show', $bundle) }}" class="bundle-product-note">
                                    <i class="lni lni-gift"></i>
                                    <span>
                                        <strong>{{ $bundle->name }}</strong>
                                        {{ $bundle->tiers->map(fn ($tier) => __('app.bundle_tier_line', ['count' => $tier->min_quantity, 'discount' => $tier->label]))->implode(' · ') }}
                                    </span>
                                </a>
                            @endforeach

                            <form id="add-to-cart-form" 
                                action="{{ route('cart.store') }}" 
                                method="post">

                                @csrf

                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="variation_id" id="variation_id">


                                <div class="row">


                                    @if($product->variations->count())

                                        @foreach($attributes as $attribute)

                                            <div class="attribute-group" data-attribute="{{ $attribute['id'] }}">

                                                <h6>{{ $attribute['name'] }}</h6>

                                                @foreach($attribute['values'] as $value)

                                                    <button
                                                        type="button"
                                                        class="attr-btn"
                                                        data-attribute="{{ $attribute['id'] }}"
                                                        data-value="{{ $value->id }}">

                                                        {{ $value->value }}

                                                    </button>

                                                @endforeach

                                            </div>

                                        @endforeach

                                    @endif


                                    <div class="col-lg-4 col-md-4 col-12">

                                        <div class="form-group quantity">
                                            <label>{{ __('app.quantity') }}</label>

                                            <div class="d-flex align-items-center gap-2">

                                                <button type="button" class="btn btn-outline-secondary" id="minus-btn">-</button>

                                                <input type="number" 
                                                    name="quantity" 
                                                    id="quantity-input"
                                                    class="form-control text-center"
                                                    min="1"
                                                    value="1"
                                                    max="{{ $product->variations->count() ? $product->default_variation->stock : $product->stock }}"
                                                    style="width: 80px;">

                                                <button type="button" class="btn btn-outline-secondary" id="plus-btn">+</button>

                                            </div>
                                        </div>

                                    </div>


                                </div>


                                <div class="bottom-content">

                                    <div class="row align-items-end">

                                        <div class="col-lg-4 col-md-4 col-12">

                                            <div class="button cart-button">

                                                <button
                                                    class="btn"
                                                    id="add-to-cart-btn"
                                                    type="submit"
                                                    style="width:100%">

                                                    {{ __('app.add_to_cart') }}

                                                </button>

                                            </div>

                                        </div>

                            </form>


                            <div class="col-lg-4 col-md-4 col-12">

                                <div class="wish-button">

                                    @auth

                                        <form action="{{ route('favorites.toggle',$product->id) }}" method="POST">

                                            @csrf

                                            <button type="submit" class="btn">

                                                <i class="lni lni-heart"></i>

                                                @if(auth()->user()->favorites->contains($product->id))

                                                    {{ __('app.remove_from_favorites') }}

                                                @else

                                                    {{ __('app.add_to_favorites') }}

                                                @endif

                                            </button>

                                        </form>

                                    @else

                                        <a href="{{ route('login') }}" class="btn">

                                            <i class="lni lni-heart"></i>

                                            {{ __('app.add_to_favorites') }}

                                        </a>

                                    @endauth

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        </div>


        <div class="product-details-info">

                        @if (Setting::get('product_ratings_visible'))
                @include('front.review.review')
            @endif


        </div>

        </div>

    </section>

    @push('page-styles')
        <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    @endpush

    @include('front.partials.product-section', [
        'products' => $suggested,
        'title' => __('app.suggested_products'),
        'text' => __('app.suggested_products_text'),
        'tinted' => true,
    ])

    @if ($bundles->isNotEmpty())
        <section class="home-section">
            <div class="container">
                <div class="home-section-head">
                    <div>
                        <h2>{{ __('app.bundles') }}</h2>
                        <p>{{ __('app.bundles_text') }}</p>
                    </div>
                    <a href="{{ route('bundles.index') }}" class="home-link">{{ __('app.view_all') }} <i class="lni lni-arrow-right"></i></a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach ($bundles as $bundle)
                        <div class="col-lg-4 col-md-6 col-12">
                            @include('front.bundles._card')
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('front.partials.product-section', [
        'products' => $featured,
        'title' => __('app.featured_products'),
        'text' => __('app.featured_products_text'),
        'link' => route('products.index', ['featured' => 1]),
        'tinted' => true,
    ])

    @include('front.partials.product-section', [
        'products' => $bestSellers,
        'title' => __('app.best_sellers'),
        'text' => __('app.best_sellers_text'),
        'link' => route('products.index', ['sort_by' => 'best_selling']),
    ])

@push('script')

@php
$variationsJs = $product->variations->map(function ($v) {
    return [
        'id'      => $v->id,
                'price'   => Currency::format($v->price),
        'compare' => (float) $v->compare_price > (float) $v->price ? Currency::format($v->compare_price) : null,
        'qty'     => $v->stock,
        'attrs'   => $v->values->pluck('id')->toArray(),
        'images'   => $v->images->pluck('image')->toArray(),
    ];
});
@endphp

<script>
/* =========================
   DATA
========================= */
const variations = @json($variationsJs);
const requiredAttributes = {{ count($attributes) }};
const selectedAttributes = {};

/* =========================
   ELEMENTS
========================= */
const priceEl = document.getElementById('product-price');
const stockEl = document.getElementById('stock-status');
const qtyInput = document.getElementById('quantity-input');
const variationInput = document.getElementById('variation_id');
const addBtn = document.getElementById('add-to-cart-btn');
const img = document.getElementById('current');
const form = document.getElementById('add-to-cart-form');
const imagesContainer = document.querySelector('#gallery .images');
const minusBtn = document.getElementById('minus-btn');
const plusBtn = document.getElementById('plus-btn');

/* =========================
   MAP VARIATIONS (FAST LOOKUP)
========================= */
const variationMap = Object.fromEntries(
    variations.map(v => [
        [...v.attrs].sort().join('-'),
        v
    ])
);

/* =========================
   ATTRIBUTES CLICK
========================= */
document.querySelectorAll('.attr-btn').forEach(btn => {

    btn.addEventListener('click', () => {

        const attributeId = btn.dataset.attribute;
        const valueId = btn.dataset.value;

        // حفظ الاختيار حسب كل attribute
        selectedAttributes[attributeId] = valueId;

        // UI active state داخل نفس المجموعة فقط
        const group = btn.closest('.attribute-group');

        group.querySelectorAll('.attr-btn')
            .forEach(b => b.classList.remove('active'));

        btn.classList.add('active');

        updateVariation();
    });

});


/* =========================
   UPDATE VARIATION
========================= */
function updateVariation() {

    const selectedValues = Object.values(selectedAttributes).map(Number);

    console.log('selected:', selectedValues);
    console.log('variations:', variations);

    if (selectedValues.length < requiredAttributes) {
        stockEl.textContent = @js(__('app.choose_options'));
        addBtn.disabled = true;
        return;
    }

    // 🔥 الحل الحقيقي
    const v = variations.find(variation => {
        return variation.attrs.length === selectedValues.length &&
               variation.attrs.every(attr => selectedValues.includes(attr));
    });

    if (!v) {
        stockEl.textContent = @js(__('app.out_of_stock'));
        addBtn.disabled = true;
        variationInput.value = '';
        return;
    }

    // ✅ SET VARIATION
    variationInput.value = v.id;

    // ✅ PRICE
    priceEl.innerHTML =
        v.price +
        (v.compare
            ? `<span class="discount-price text-muted text-decoration-line-through">${v.compare}</span>`
            : '');

    // ✅ STOCK
    stockEl.innerHTML = v.qty > 0
        ? `<span class="text-success">${@js(Setting::get('show_stock') ? __('app.in_stock_count') : __('app.in_stock')).replace(':count', v.qty)}</span>`
        : `<span class="text-danger">${@js(__('app.out_of_stock'))}</span>`;

    // ✅ QUANTITY
    qtyInput.max = v.qty;
    qtyInput.value = 1;
    qtyInput.disabled = v.qty <= 0;

    plusBtn.disabled = v.qty <= 0;
    minusBtn.disabled = v.qty <= 0;

    // ✅ BUTTON
    addBtn.disabled = v.qty <= 0;

    // ✅ IMAGE
    imagesContainer.innerHTML = '';

    if (v.images && v.images.length > 0) {

        // صورة رئيسية
        img.src = "{{ asset('storage') }}/" + v.images[0];

        // thumbnails
        v.images.forEach(image => {

            const el = document.createElement('img');

            el.src = "{{ asset('storage') }}/" + image;
            el.classList.add('img');

            el.onclick = () => {
                img.src = el.src;
            };

            imagesContainer.appendChild(el);
        });

    } else {

        // 🔥 fallback لو مفيش صور للفارييشن
        const productImages = @json($product->images->pluck('image'));

        if (productImages.length > 0) {

            img.src = "{{ asset('storage') }}/" + productImages[0];

            productImages.forEach(image => {

                const el = document.createElement('img');

                el.src = "{{ asset('storage') }}/" + image;
                el.classList.add('img');

                el.onclick = () => {
                    img.src = el.src;
                };

                imagesContainer.appendChild(el);
            });
        }
    }
}
/* =========================
   QTY CONTROL
========================= */

minusBtn.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || 1;
    if (val > 1) {
         qtyInput.value = val - 1;
    }
});



plusBtn.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || 1;
    let max = parseInt(qtyInput.max) || 1;

    if (val < max) {
        qtyInput.value = val + 1;
    }
});

qtyInput.addEventListener('input', () => {
    let val = parseInt(qtyInput.value) || 1;

    if (val < 1) val = 1;
    if (val > parseInt(qtyInput.max)) val = parseInt(qtyInput.max);

    qtyInput.value = val;
});

/* =========================
   AUTO SELECT FIRST OPTIONS
========================= */
if (variations.length > 0) {

    document.querySelectorAll('.attribute-group').forEach(group => {

        const btn = group.querySelector('.attr-btn');

        if (btn) {
            btn.click(); // هذا الآن يشتغل صح لأنه يملأ selectedAttributes
        }

    });

} else {
    qtyInput.max = {{ $product->stock ?? 1 }};
}

/* =========================
   AJAX ADD TO CART
========================= */
form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (variations.length > 0 && !variationInput.value) {
        showToast(@js(__('app.choose_options')), 'error');
        return;
    }

    const formData = new FormData(form);

    addBtn.disabled = true;
    addBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + @js(__('app.adding_to_cart'));

    fetch(form.action, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Accept": "application/json"
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {

            showToast(data.message || @js(__('app.added_to_cart')));

            document.querySelectorAll('.cart-count-display')
                .forEach(el => el.innerText = data.count);

            if (typeof loadCart === "function") {
                loadCart();
            } else {
                // fallback سريع
                document.querySelectorAll('.cart-count-display')
                    .forEach(el => el.innerText = data.count);
            }

        } else {
            showToast(@js(__('app.something_went_wrong')), 'error');
        }

    })
    .catch(() => {
        showToast(@js(__('app.something_went_wrong')), 'error');
    })
    .finally(() => {
        addBtn.disabled = false;
        addBtn.innerText = "{{ __('app.add_to_cart') }}";
    });
});


/* =========================
   TOAST
========================= */
function showToast(message, type = 'success') {

    const toast = document.createElement('div');

    toast.innerText = message;

    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.padding = '12px 20px';
    toast.style.color = '#fff';
    toast.style.borderRadius = '8px';
    toast.style.zIndex = 9999;
    toast.style.background = type === 'success' ? '#28a745' : '#dc3545';

    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}

</script>

@endpush

</x-front-layout>
