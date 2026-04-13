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

                                    {{ $product->stock > 0 ? 'متوفر ('.$product->stock.')':'غير متوفر' }}

                                </p>

                            @endif


                            <p class="info-text">{{ $product->description }}</p>

                            <p id="stock-status"></p>


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

                                            <select class="form-control" name="quantity" id="quantity-select">

                                                @if($product->variations->count())

                                                    <option>1</option>

                                                @else

                                                    @for($i=1;$i<=$product->stock;$i++)

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

            @include('front.review.review')

        </div>

        </div>

    </section>


    @push('script')

        @php

            $variationsJs = $product->variations->map(function ($v) {

                return [

                    'id'=>$v->id,
                    'price'=>$v->price,
                    'compare'=>$v->compare_price,
                    'qty'=>$v->stock,
                    'attrs'=>$v->values->pluck('id')->toArray(),
                    'image'=>$v->image

                ];

            });

        @endphp


       <script>

    const variations = @json($variationsJs);
    const requiredAttributes = {{ count($attributes) }};

    const priceEl = document.getElementById('product-price');
    const stockEl = document.getElementById('stock-status');
    const qty = document.getElementById('quantity-select');
    const variationInput = document.getElementById('variation_id');
    const addBtn = document.getElementById('add-to-cart-btn');
    const img = document.getElementById('current');
    const form = document.getElementById('add-to-cart-form');

    // =========================
    // اختيار الخصائص (variation)
    // =========================
    document.querySelectorAll('.attr-btn').forEach(btn => {

        btn.addEventListener('click', () => {

            const group = btn.closest('.attribute-group');

            group.querySelectorAll('.attr-btn').forEach(b => b.classList.remove('active'));

            btn.classList.add('active');

            updateVariation();

        });

    });


    function updateVariation() {

        const selected = [...document.querySelectorAll('.attr-btn.active')]
            .map(b => parseInt(b.dataset.value));

        if (selected.length < requiredAttributes) return;

        const v = variations.find(v =>
            selected.every(val => v.attrs.includes(val))
        );

        if (!v) {

            stockEl.innerText = 'غير متوفر';
            addBtn.disabled = true;
            variationInput.value = '';

            return;
        }

        variationInput.value = v.id;

        priceEl.innerHTML = v.price +
            (v.compare ? `<span class="discount-price text-muted text-decoration-line-through">${v.compare}</span>` : '');

        stockEl.innerText = 'متوفر (' + v.qty + ')';

        qty.innerHTML = '';

        for (let i = 1; i <= v.qty; i++) {
            qty.innerHTML += `<option value="${i}">${i}</option>`;
        }

        addBtn.disabled = v.qty <= 0;

        if (v.image) {
            img.src = '/storage/' + v.image;
        }

    }


    // =========================
    // AJAX ADD TO CART 🔥
    // =========================
    form.addEventListener('submit', function (e) {

        e.preventDefault();

        // ✅ منع الإرسال لو مفيش variation
        if (variations.length > 0 && !variationInput.value) {
            alert('من فضلك اختر الخصائص أولاً');
            return;
        }

        const formData = new FormData(form);

        addBtn.disabled = true;
        addBtn.innerText = 'جاري الإضافة...';

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
                // ✅ إظهار رسالة النجاح
                showToast(data.message || 'تمت الإضافة بنجاح');

                // ✅ تحديث جميع عدادات الكارت في الصفحة
                // استخدمنا الكلاس cart-count-display الذي اتفقنا عليه سابقاً
                document.querySelectorAll('.cart-count-display').forEach(el => {
                    el.innerText = data.count; // الـ Controller يرسل count
                });

                // ✅ تحديث السلة المصغرة (Dropdown)
                if (typeof loadCart === "function") {
                    loadCart();
                }

            } else {
                showToast('حدث خطأ أثناء الإضافة', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('خطأ في الاتصال', 'error');
        })
        .finally(() => {
            addBtn.disabled = false;
            addBtn.innerText = "{{ __('app.add_to_cart') }}";
        });

    });


    // =========================
    // Toast بسيط 👌
    // =========================
    function showToast(message, type = 'success') {

        let toast = document.createElement('div');

        toast.innerText = message;

        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.padding = '12px 20px';
        toast.style.background = type === 'success' ? '#28a745' : '#dc3545';
        toast.style.color = '#fff';
        toast.style.borderRadius = '8px';
        toast.style.zIndex = '9999';

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

</script>

    @endpush


</x-front-layout>
