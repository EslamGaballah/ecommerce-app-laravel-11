@extends('layouts.dashboard')

@section('title', __('app.settings'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __('app.settings') }}</li>
@endsection

@section('content')
<x-alert type="success" />

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $tabs = [
        'general' => ['icon' => 'lni-cog', 'label' => __('app.settings_general')],
        'contact' => ['icon' => 'lni-phone', 'label' => __('app.settings_contact')],
        'social' => ['icon' => 'lni-share', 'label' => __('app.settings_social')],
        'store' => ['icon' => 'lni-package', 'label' => __('app.settings_store')],
        'reviews' => ['icon' => 'lni-star', 'label' => __('app.settings_reviews')],
        'checkout' => ['icon' => 'lni-cart', 'label' => __('app.settings_payments')],
        'maintenance' => ['icon' => 'lni-construction', 'label' => __('app.settings_maintenance')],
    ];
@endphp

<form action="{{ route('dashboard.settings.update') }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')

    <ul class="nav nav-pills settings-tabs mb-3" role="tablist">
        @foreach ($tabs as $id => $tab)
            <li class="nav-item">
                <a @class(['nav-link', 'active' => $loop->first]) id="tab-{{ $id }}" data-toggle="pill" href="#{{ $id }}" role="tab" aria-controls="{{ $id }}">
                    <i class="lni {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                    @if ($id === 'maintenance' && $settings['maintenance_mode'])
                        <span class="badge bg-danger">{{ __('app.maintenance_on') }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        {{-- General --}}
        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="tab-general">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-group">
                        <x-form.input label="{{ __('app.site_name') }}" name="site_name" :value="$settings['site_name']" required maxlength="100" />
                    </div>
                    <div class="form-group">
                        <label for="logo">{{ __('app.logo') }}</label>
                        @if ($settings['logo'])
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$settings['logo']) }}" alt="{{ $settings['site_name'] }}" style="max-height: 60px;">
                            </div>
                        @endif
                        <input type="file" id="logo" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <small class="text-muted">{{ __('app.logo_help') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="site_description">{{ __('app.site_description') }}</label>
                        <textarea id="site_description" name="site_description" class="form-control" rows="2" maxlength="300">{{ old('site_description', $settings['site_description']) }}</textarea>
                        <small class="text-muted">{{ __('app.site_description_help') }}</small>
                    </div>
                    <div class="form-group mb-0">
                        <x-form.input label="{{ __('app.announcement') }}" name="announcement" :value="$settings['announcement']" maxlength="150" />
                        <small class="text-muted">{{ __('app.announcement_help') }}</small>
                    </div>
                    <hr>
                    @include('dashboard.settings._toggle', ['name' => 'dark_mode_enabled', 'label' => __('app.dark_mode_enabled'), 'help' => __('app.dark_mode_enabled_help')])
                    <div class="form-group mb-0">
                        <label for="default_theme">{{ __('app.default_theme') }}</label>
                        <select id="default_theme" name="default_theme" class="form-control">
                            @foreach (['light' => __('app.theme_light'), 'dark' => __('app.theme_dark'), 'system' => __('app.theme_system')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('default_theme', $settings['default_theme']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="tab-contact">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-group">
                        <x-form.input label="{{ __('app.phone') }}" name="phone" :value="$settings['phone']" />
                    </div>
                    <div class="form-group">
                        <x-form.input label="{{ __('app.email') }}" type="email" name="contact_email" :value="$settings['contact_email']" />
                    </div>
                    <div class="form-group">
                        <x-form.input label="{{ __('app.address') }}" name="address" :value="$settings['address']" />
                    </div>
                    <div class="form-group">
                        <x-form.input label="{{ __('app.working_hours') }}" name="working_hours" :value="$settings['working_hours']" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Social media --}}
        <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="tab-social">
            <div class="card mb-4">
                <div class="card-body">
                    <p class="text-muted">{{ __('app.settings_social_help') }}</p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.whatsapp') }}" name="whatsapp" :value="$settings['whatsapp']" />
                            <small class="text-muted">{{ __('app.whatsapp_help') }}</small>
                        </div>
                        @foreach (array_keys(App\Models\Setting::SOCIAL_NETWORKS) as $network)
                            <div class="col-md-6 form-group">
                                <x-form.input label="{{ __('app.'.$network) }}" type="url" placeholder="https://" dir="ltr" name="{{ $network }}" :value="$settings[$network]" />
                            </div>
                        @endforeach
                    </div>
                    <hr>
                    @include('dashboard.settings._toggle', ['name' => 'show_social_header', 'label' => __('app.show_social_header')])
                    @include('dashboard.settings._toggle', ['name' => 'show_social_footer', 'label' => __('app.show_social_footer')])
                </div>
            </div>
        </div>

        {{-- Store --}}
        <div class="tab-pane fade" id="store" role="tabpanel" aria-labelledby="tab-store">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-group">
                        <label for="currency">{{ __('app.default_currency') }}</label>
                        <select id="currency" name="currency" class="form-control">
                            @foreach (App\Models\Setting::CURRENCIES as $code)
                                <option value="{{ $code }}" @selected(old('currency', $settings['currency']) === $code)>{{ __('app.currency_'.$code) }} ({{ $code }})</option>
                            @endforeach
                        </select>
                        <small class="text-warning d-block mt-1"><strong>{{ __('app.default_currency_warning') }}</strong></small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.products_per_page') }}" type="number" min="3" max="60" name="products_per_page" :value="$settings['products_per_page']" required />
                        </div>
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.low_stock_threshold') }}" type="number" min="0" name="low_stock_threshold" :value="$settings['low_stock_threshold']" required />
                            <small class="text-muted">{{ __('app.low_stock_threshold_help') }}</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <x-form.input label="{{ __('app.stagnant_days') }}" type="number" min="1" name="stagnant_days" :value="$settings['stagnant_days']" required />
                        <small class="text-muted">{{ __('app.stagnant_days_help') }}</small>
                    </div>
                    @include('dashboard.settings._toggle', ['name' => 'show_stock', 'label' => __('app.show_stock'), 'help' => __('app.show_stock_help')])
                    @include('dashboard.settings._toggle', ['name' => 'registration_enabled', 'label' => __('app.registration_enabled'), 'help' => __('app.registration_enabled_help')])
                </div>
            </div>
        </div>

        {{-- Reviews and comments --}}
        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="tab-reviews">
            <div class="card mb-4">
                <div class="card-body">
                    @include('dashboard.settings._toggle', ['name' => 'product_ratings_visible', 'label' => __('app.product_ratings_visible'), 'help' => __('app.product_ratings_visible_help')])
                    @include('dashboard.settings._toggle', ['name' => 'product_reviews_enabled', 'label' => __('app.product_reviews_enabled'), 'help' => __('app.product_reviews_enabled_help')])
                    @include('dashboard.settings._toggle', ['name' => 'blog_comments_enabled', 'label' => __('app.blog_comments_enabled'), 'help' => __('app.blog_comments_enabled_help')])
                </div>
            </div>
        </div>

        {{-- Checkout --}}
        <div class="tab-pane fade" id="checkout" role="tabpanel" aria-labelledby="tab-checkout">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-group">
                        <x-form.input label="{{ __('app.settings_tax') }}" type="number" step="0.01" min="0" name="tax" :value="$settings['tax']" required />
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.min_order_amount') }}" type="number" step="0.01" min="0" name="min_order_amount" :value="$settings['min_order_amount']" />
                            <small class="text-muted">{{ __('app.leave_empty_to_disable') }}</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.free_shipping_min') }}" type="number" step="0.01" min="0" name="free_shipping_min" :value="$settings['free_shipping_min']" />
                            <small class="text-muted">{{ __('app.leave_empty_to_disable') }}</small>
                        </div>
                    </div>
                    @include('dashboard.settings._toggle', ['name' => 'payment_cash_enabled', 'label' => __('app.payment_cash')])
                    @include('dashboard.settings._toggle', ['name' => 'payment_card_enabled', 'label' => __('app.payment_visa')])

                    <hr>
                    <h6>{{ __('app.cod_limits') }}</h6>
                    <p class="text-muted small">{{ __('app.cod_limits_help') }}</p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.cod_max_items') }}" type="number" step="1" min="1" name="cod_max_items" :value="$settings['cod_max_items']" />
                            <small class="text-muted">{{ __('app.leave_empty_to_disable') }}</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <x-form.input label="{{ __('app.cod_max_amount') }}" type="number" step="0.01" min="0.01" name="cod_max_amount" :value="$settings['cod_max_amount']" />
                            <small class="text-muted">{{ __('app.leave_empty_to_disable') }}</small>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted">{{ __('app.settings_test_payments_help') }}</small>
                </div>
            </div>
        </div>

        {{-- Maintenance --}}
        <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="tab-maintenance">
            <div class="card mb-4">
                <div class="card-body">
                    @include('dashboard.settings._toggle', ['name' => 'maintenance_mode', 'label' => __('app.maintenance_mode'), 'help' => __('app.maintenance_mode_help')])
                    <div class="form-group mb-0">
                        <label for="maintenance_message">{{ __('app.maintenance_message') }}</label>
                        <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="3" maxlength="500" placeholder="{{ __('app.maintenance_default_message') }}">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mb-4">{{ __('app.save') }}</button>
</form>
@endsection

@push('script')
<script>
    // Return to the same tab after saving, and open the tab named in the address or holding an error.
    (function () {
        const key = 'settings-tab';
        const read = () => { try { return sessionStorage.getItem(key); } catch (e) { return null; } };
        const invalid = document.querySelector('.tab-pane .is-invalid');
        const wanted = invalid ? '#' + invalid.closest('.tab-pane').id : (window.location.hash || read());
        const tab = wanted && document.querySelector('.settings-tabs a[href="' + wanted + '"]');

        if (tab) $(tab).tab('show');

        // The maintenance banner links here, and on this page that only changes the address hash.
        window.addEventListener('hashchange', function () {
            const target = document.querySelector('.settings-tabs a[href="' + window.location.hash + '"]');

            if (target) $(target).tab('show');
        });

        $('.settings-tabs a').on('shown.bs.tab', function (e) {
            try { sessionStorage.setItem(key, e.target.getAttribute('href')); } catch (error) {}
        });
    })();
</script>
@endpush
