
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

    <!-- Start Item Details -->
    <section class="item-details section">
        <div class="container">
            <div class="top-area">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="product-images">
                            <main id="gallery">

                                <div class="main-img">
                                    <img 
                                        id="current" 
                                        src="{{ asset('storage/' . ( $defaultVariation?->image ?? $product->image)) }}" 
                                        alt="{{ $product->name }}"
                                    >
                                </div>
                                <div class="images">
                                    @if($defaultVariation && $defaultVariation->images->count())
                                        @foreach($defaultVariation->images as $image)

                                            <img
                                                src="{{ asset('storage/' . $image->path) }}"
                                                class="img"
                                                onclick="document.getElementById('current').src=this.src"
                                            >
                                        @endforeach
                                    @endif
                                </div>

                            </main>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="product-info">
                            <h2 class="title">{{ $product->name }}</h2>
                            <p class="category">
                                <i class="lni lni-tag"></i>
                                    {{ __('app.category') }}:<a href="javascript:void(0)"></a>
                                    {{-- {{ $product->category?->name }} --}}
                                    {{ $product->category->name ?? 'Uncategorized' }}
                            </p>
                            <h3 class="price" id="product-price">

                                {{ Currency::format($product->final_price) }} 

                                @if($product->final_compare_price && $product->final_compare_price > $product->final_price)
                                    <span class="discount-price text-muted text-decoration-line-through">
                                        {{ Currency::format($product->final_compare_price) }}
                                    </span>
                                @endif

                            </h3>

                            @if(!$product->variations->count())
                                <p class="mt-2 {{ $product->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $product->quantity > 0 ? 'متوفر (' . $product->quantity . ')' : 'غير متوفر' }}
                                </p>
                            @endif
                            <p class="info-text">{{ $product->description }}</p>
                            <p id="stock-status" class="mt-2 text-muted"></p>


                            {{--start add to cart --}}
                            <form action="{{ route('cart.store') }}" method="post">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="variation_id" id="variation_id">

                                <div class="row ">
                                 {{-- @foreach($attributes as $attributeName => $values)
                                    <div class="mb-3">
                                        <label class="fw-bold">{{ ucfirst($attributeName) }}</label>

                                        <div class="d-flex gap-2 mt-2">
                                            @foreach($values->unique('value') as $value)
                                                <label class="variation-radio">
                                                    <input type="radio"
                                                            name="{{ $attributeName }}"
                                                            class="variation-option"
                                                            data-attribute="{{ $attributeName }}"
                                                            data-value="{{ $value->value }}">
                                                    <span>{{ $value->value }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach --}}
                                @if($product->variations->count())
                                    @foreach($attributes as $attribute)
                                        <div class="attribute-group" data-attribute="{{ $attribute['id'] }}">
                                            <h6>{{ $attribute['name'] }}</h6>

                                            @foreach($attribute['values'] as $value)
                                                <button
                                                    type="button"
                                                    class="attr-btn"
                                                    data-attribute="{{ $attribute['id'] }}"
                                                    data-value="{{ $value->id }}"
                                                >
                                                    {{ $value->value }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @endif


                                    <div class="col-lg-4 col-md-4 col-12">
                                        

                                        <div class="form-group quantity">
                                            <label for="color">{{ __('app.quantity') }}</label>
                                            {{-- <select class="form-control" name="quantity" id="quantity-select"></select> --}}

                                            <select class="form-control" name="quantity" id="quantity-select">
                                                @if($product->variations->count())
                                                    <option>1</option>
                                                @else
                                                    @for($i = 1; $i <= $product->quantity; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="bottom-content">
                                    <div class="row align-items-end">
                                        <div class="col-lg-4 col-md-4 col-12">
                                            <div class="button cart-button">
                                                <button class="btn" 
                                                id="add-to-cart-btn"
                                                type="submit"
                                                 style="width: 100%;">{{ __('app.add_to_cart') }}</button>
                                            </div>
                                        </div>
                            </form>

                                        <div class="col-lg-4 col-md-4 col-12">
                                            <div class="wish-button">
                                                
                                                @auth
                                                <form action="{{ route('favorites.toggle', $product->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn"><i class="lni lni-heart"></i>
                                                        @if(auth()->user()->favorites->contains($product->id))
                                                            {{ __('app.remove_from_favorites') }}
                                                        @else
                                                            {{ __('app.add_to_favorites') }}
                                                        @endif
                                                    </button>
                                                </form>
                                                @else
                                                <a href="{{ route('login') }}" 
                                                class="btn"><i class="lni lni-heart"></i> 
                                                    {{ __('app.add_to_favorites') }}</a>
                                                @endauth

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{--start add to cart --}}
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="product-details-info">
                {{-- <div class="single-block">
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="info-body custom-responsive-margin">
                                <h4>Details</h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                                    incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
                                    exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                                    irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.</p>
                                <h4>Features</h4>
                                <ul class="features">
                                    <li>Capture 4K30 Video and 12MP Photos</li>
                                    <li>Game-Style Controller with Touchscreen</li>
                                    <li>View Live Camera Feed</li>
                                    <li>Full Control of HERO6 Black</li>
                                    <li>Use App for Dedicated Camera Operation</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="info-body">
                                <h4>Specifications</h4>
                                <ul class="normal-list">
                                    <li><span>Weight:</span> 35.5oz (1006g)</li>
                                    <li><span>Maximum Speed:</span> 35 mph (15 m/s)</li>
                                    <li><span>Maximum Distance:</span> Up to 9,840ft (3,000m)</li>
                                    <li><span>Operating Frequency:</span> 2.4GHz</li>
                                    <li><span>Manufacturer:</span> GoPro, USA</li>
                                </ul>
                                <h4>Shipping Options:</h4>
                                <ul class="normal-list">
                                    <li><span>Courier:</span> 2 - 4 days, $22.50</li>
                                    <li><span>Local Shipping:</span> up to one week, $10.00</li>
                                    <li><span>UPS Ground Shipping:</span> 4 - 6 days, $18.00</li>
                                    <li><span>Unishop Global Export:</span> 3 - 4 days, $25.00</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> --}}

                @include('front.review.review');

           
            </div>
        </div>
    </section>
    <!-- End Item Details -->

    <!-- Review Modal -->
    <div class="modal fade review-modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('app.leave_review') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="review-name">Your Name</label>
                                <input class="form-control" type="text" id="review-name" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="review-email">Your Email</label>
                                <input class="form-control" type="email" id="review-email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="review-subject">Subject</label>
                                <input class="form-control" type="text" id="review-subject" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="review-rating">Rating</label>
                                <select class="form-control" id="review-rating">
                                    <option>5 Stars</option>
                                    <option>4 Stars</option>
                                    <option>3 Stars</option>
                                    <option>2 Stars</option>
                                    <option>1 Star</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="review-message">Review</label>
                        <textarea class="form-control" id="review-message" rows="8" required></textarea>
                    </div>
                </div>
                <div class="modal-footer button">
                    <button type="button" class="btn">Submit Review</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Review Modal -->

@push('script')

@php

    $variationsJs = $product->variations->map(function ($v) {
    return [
        'id' => $v->id,
        'attributes_ids' => $v->values->pluck('id')->toArray() ?? [],
    ];
});


@endphp

{{-- auto select primary variation --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ===============================
       عناصر الصفحة
    =============================== */
    const addBtn      = document.getElementById('add-to-cart-btn');
    const priceEl     = document.getElementById('product-price');
    const stockEl     = document.getElementById('stock-status');
    const qtySelect   = document.getElementById('quantity-select');
    const variationId = document.getElementById('variation_id');
    const mainImg     = document.getElementById('current');

    /* ===============================
       Variations من Laravel
    =============================== */
    const variations = @json($variationsJs);

    const variations = @json($variationsJs);

    if (!variations.length) {
        document.getElementById('add-to-cart-btn').disabled = false;
    }
    
    const requiredAttributes = {{ count($attributes) }};

    /* ===============================
       Auto select primary variation
    =============================== */
    const primaryValues = @json(
        optional(optional($product->defaultVariation)->values)
        ->pluck('id')
    );

    if (primaryValues) {
        primaryValues.forEach(valueId => {
            const btn = document.querySelector(`.attr-btn[data-value="${valueId}"]`);
            if (btn) btn.classList.add('active');
        });

        updateVariation();
    }

    /* ===============================
       Attribute buttons click
    =============================== */
    document.querySelectorAll('.attr-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            if (this.classList.contains('disabled')) return;

            const group = this.closest('.attribute-group');

            group.querySelectorAll('.attr-btn')
                .forEach(b => b.classList.remove('active'));

            this.classList.add('active');

            updateVariation();
        });
    });

    /* ===============================
       Core logic
    =============================== */
    function updateVariation() {

        const selectedValues = getSelectedValues();

        disableInvalidButtons(selectedValues);

        if (Object.keys(selectedValues).length < requiredAttributes) {
            resetUI();
            return;
        }


        showLoading();

        fetch('{{ route("variations.match", $product->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                attributes: selectedValues
            })
        })
        .then(res => res.json())
        .then(data => {

            hideLoading();

            if (!data.exists) {
                addBtn.disabled = true;
                stockEl.innerText = 'غير متاح';
                stockEl.className = 'text-danger';
                return;
            }

            variationId.value = data.id;
            addBtn.disabled = false;

            updatePrice(data.price, data.compare_price);
            updateStock(data.quantity);
            updateQuantity(data.quantity);
            updateImages(data.images);
        })
        .catch(() => {
            hideLoading();
            addBtn.disabled = true;
        });
    }

    /* ===============================
       Helpers
    =============================== */
    function getSelectedValues() {
        const obj = {};
        document.querySelectorAll('.attr-btn.active').forEach(btn => {
            obj[btn.dataset.attribute] = btn.dataset.value;
        });
        return obj;
    }


    function disableInvalidButtons(selected) {

        document.querySelectorAll('.attr-btn').forEach(btn => {

            const attrId  = btn.dataset.attribute;
            const valueId = parseInt(btn.dataset.value);

            const test = { ...selected, [attrId]: valueId };

            const valid = variations.some(v =>
                Object.values(test).every(val =>
                    v.attributes_ids.includes(parseInt(val))
                )
            );

            btn.disabled = !valid;
            btn.classList.toggle('disabled', !valid);
        });
    }


    function updatePrice(price, compare = null) {
        priceEl.innerHTML = price +
            (compare ? ` <span class="discount-price">${compare}</span>` : '');
    }

    function updateStock(quantity) {
        if (quantity > 0) {
            stockEl.innerText = `متوفر (${quantity})`;
            stockEl.className = 'text-success';
        } else {
            stockEl.innerText = 'غير متوفر';
            stockEl.className = 'text-danger';
            addBtn.disabled = true;
        }
    }

    function updateQuantity(quantity) {
        qtySelect.innerHTML = '';
        for (let i = 1; i <= quantity; i++) {
            qtySelect.innerHTML += `<option value="${i}">${i}</option>`;
        }
    }

    function updateImages(images) {
        if (!images || !images.length) return;

        const normalize = p => p.replace(/^storage\//, '').replace(/^\//, '');

        mainImg.src = `{{ asset('storage') }}/${normalize(images[0])}`;

        const container = document.querySelector('.images');
        if (!container) return;

        container.innerHTML = '';

        images.forEach(img => {
            const el = document.createElement('img');
            el.src = `{{ asset('storage') }}/${normalize(img)}`;
            el.classList.add('img');
            el.onclick = () => mainImg.src = el.src;
            container.appendChild(el);
        });
    }

    function resetUI() {
        addBtn.disabled = true;
        stockEl.innerText = '';
    }

    /* ===============================
       UX Loading
    =============================== */
    function showLoading() {
        priceEl.classList.add('opacity-50');
    }

    function hideLoading() {
        priceEl.classList.remove('opacity-50');
    }

});
</script>

@endpush

</x-front-layout>