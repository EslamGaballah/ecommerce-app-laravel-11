<x-front-layout title="Checkout">

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

        <x-slot:breadcrumb>
            <div class="breadcrumbs">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="breadcrumbs-content">
                                <h1 class="page-title">{{ __('app.checkout') }}</h1>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <ul class="breadcrumb-nav">
                                <li><a href="{{ route('products.index') }}">{{ __('app.shop') }}</a></li>
                                <li>{{ __('app.checkout') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:breadcrumb>

        <!--====== Checkout Form Steps Part Start ======-->

        <section class="checkout-wrapper section">
             <form action="{{ route('checkout.store') }}" method="post" >
                            @csrf
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">

                            <div class="checkout-steps-form-style-1">
                                <ul id="accordionExample">
                                    <li>
                                        <h6 class="title" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">{{ __('app.shipping_address') }} </h6>
                                        <section class="checkout-steps-form-content collapse show" id="collapseThree" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="single-form form-default">
                                                        <label>{{ __('app.user_name') }}</label>
                                                        <div class="row">
                                                            <div class="col-md-6 form-input form">
                                                                <x-form.input name="first_name" placeholder="{{ __('app.first_name') }} " />
                                                            </div>
                                                            <div class="col-md-6 form-input form">
                                                                <x-form.input name="last_name" placeholder=" {{ __('app.last_name') }}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="single-form form-default">
                                                        <label>{{ __('app.email_address') }}</label>
                                                        <div class="form-input form">
                                                            <x-form.input name="email" placeholder="{{ __('app.checkout') }} " />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="single-form form-default">
                                                        <label>{{ __('app.phone_number') }}</label>
                                                        <div class="form-input form">
                                                            <x-form.input name="phone_number" placeholder=" {{ __('app.phone_number') }}" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="single-form form-default">
                                                        <label> {{ __('app.street_address') }}</label>
                                                        <div class="form-input form">
                                                            <x-form.input name="street_address" placeholder=" {{ __('app.street_address') }}" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="single-form form-default">
                                                        <label>{{__('app.governorate')}}</label>
                                                        <select name="governorate_id" id="governorate_id" class="form-control">
                                                            <option value="" >  {{ __('app.choose_governorate') }}</option>
                                                            @foreach($governorates as $gov)
                                                                <option
                                                                    value="{{ $gov->id }}"
                                                                    data-price="{{ $gov->shipping_price }}">
                                                                    {{ $gov->name }} - شحن {{ $gov->shipping_price }} جنيه
                                                                    ({{ $gov->delivery_days }} أيام)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="single-form form-default">
                                                        <label>{{__('app.city')}}</label>
                                                        <div class="form-input form">
                                                            <x-form.input name="city" placeholder="{{__('app.city')}}" />
                                                        </div>
                                                    </div>
                                                </div>

                                                 <div class="col-md-6">
                                                    <div class="single-form form-default">
                                                        <label>{{__('app.country')}}</label>
                                                        <div class="select-items">
                                                            <x-form.input name="country" placeholder="{{__('app.country')}}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </li>
                                </ul>
                            </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="checkout-sidebar">

                           <div class="checkout-sidebar-coupon">
                                <p>{{__('app.apply_coupon')}}</p>

                                <div class="single-form form-default">

                                    <div class="form-input form">
                                        <input type="text" id="coupon_code" placeholder="Enter Coupon Code">
                                    </div>

                                    <div class="button">
                                        <button type="button" id="applyCouponBtn" class="btn btn-primary">
                                            {{__('app.apply_coupon')}}
                                        </button>
                                    </div>

                                    <p id="coupon-message" class="mt-2"></p>

                                </div>
                            </div>

                            <div class="checkout-sidebar-price-table mt-30">
                                    <h5 class="title">{{__('app.pricing_table')}}</h5>

                                    <div class="sub-total-price">

                                        <div class="total-price">
                                            <p class="value">{{__('app.subtotal_price')}}:</p>
                                            <p class="price" id="subtotal">
                                                {{ Currency::format($totals['original']) }}
                                            </p>
                                        </div>

                                        <div class="total-price discount">
                                            <p class="value">{{__('app.discount')}}:</p>
                                            <p class="price" id="discount">
                                                {{ Currency::format($totals['discount']) }}
                                            </p>
                                        </div>

                                        <div class="total-price">
                                            <p class="value">{{__('app.tax')}}:</p>
                                            <p class="price" id="tax">
                                                {{ Currency::format($totals['tax'] ?? 0) }}
                                            </p>
                                        </div>

                                        <div class="total-price">
                                            <p class="value">{{__('app.shipping_price')}}:</p>
                                            <p class="price" id="shipping">
                                                {{ Currency::format($totals['shipping'] ?? 0) }}
                                            </p>
                                        </div>

                                    </div>

                                    <div class="total-payable">
                                        <div class="payable-price">
                                            <p class="value"> {{__('app.total_payable')}}:</p>
                                            <p class="price" id="total">
                                                {{ Currency::format($totals['total']) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary">{{__('app.checkout')}}</button>
                                </div>


                            </div>
                            {{-- <div class="checkout-sidebar-banner mt-30">
                                <a href="product-grids.html">
                                    <img src="https://via.placeholder.com/400x330" alt="#">
                                </a>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
             </form>
        </section>
@push('script')
<script>
    // 1. دالة موحدة لتحديث جدول الأسعار في الواجهة
    function updatePricingTable(totals) {
        if (!totals) return;
        
        document.getElementById('subtotal').innerText = totals.original;
        document.getElementById('discount').innerText = totals.discount;
        document.getElementById('tax').innerText = totals.tax;
        document.getElementById('shipping').innerText = totals.shipping;
        document.getElementById('total').innerText = totals.total;
    }

    // 2. دالة إرسال البيانات للسيرفر (AJAX)
    async function applyCheckoutUpdates(code, governorateId, triggerBtn = null) {
        // حالة التحميل
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Applying...';
        }

        try {
            const response = await fetch('/checkout/apply-coupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    code: code,
                    governorate_id: governorateId
                })
            });

            const data = await response.json();

            if (data.error) {
                const msgElement = document.getElementById('coupon-message');
                if (msgElement && code) { // أظهر الخطأ فقط لو كان المستخدم يحاول إدخال كوبون
                    msgElement.innerText = data.error;
                    msgElement.style.color = 'red';
                }
            } else {
                // نجاح العملية
                const msgElement = document.getElementById('coupon-message');
                if (msgElement && code) {
                    msgElement.innerText = "Coupon applied successfully!";
                    msgElement.style.color = 'green';
                }
                // تحديث الأرقام
                updatePricingTable(data.totals);
            }

        } catch (error) {
            console.error('Error:', error);
        } finally {
            // إنهاء حالة التحميل
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerText = 'Apply Coupon';
            }
        }
    }

    // 3. مستمع الحدث لزر الكوبون
    document.getElementById('applyCouponBtn').addEventListener('click', function() {
        let code = document.getElementById('coupon_code').value || null;
        let governorate = document.getElementById('governorate_id').value;

        if (!code) {
            document.getElementById('coupon-message').innerText = "Please enter coupon code";
            document.getElementById('coupon-message').style.color = 'orange';
            return;
        }

        applyCheckoutUpdates(code, governorate, this);
    });

    // 4. مستمع الحدث لتغيير المحافظة (تحديث الشحن)
    document.getElementById('governorate_id').addEventListener('change', function() {
        let code = document.getElementById('coupon_code').value;
        let governorateId = this.value;

        if (governorateId) {
            // نقوم بالتحديث حتى لو الكوبون فارغ لتحديث سعر الشحن فقط
            applyCheckoutUpdates(code, governorateId);
        }
    });
</script>

@endpush

</x-front-layout>
