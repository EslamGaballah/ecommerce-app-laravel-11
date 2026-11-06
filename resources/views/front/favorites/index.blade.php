<x-front-layout :title="__('app.favorites')">
    @push('page-styles')
        <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    @endpush

    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => __('app.favorites')])
    </x-slot:breadcrumb>

    <section class="home-section home-section-tinted">
        <div class="container">
            <x-alert type="success" />

            <div id="favorites-list" @class(['row home-products', 'd-none' => $favorites->isEmpty()])>
                @foreach ($favorites as $product)
                    <div class="col-lg-3 col-md-4 col-6" data-favorite-item>
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <div id="favorites-empty" @class(['info-page-card text-center', 'd-none' => $favorites->isNotEmpty()])>
                <i class="lni lni-heart favorites-empty-icon"></i>
                <h2>{{ __('app.no_favorites') }}</h2>
                <p>{{ __('app.no_favorites_text') }}</p>
                <div class="button"><a href="{{ route('products.index') }}" class="btn">{{ __('app.shop_now') }}</a></div>
            </div>

            <div class="mt-3">
                {{ $favorites->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
</x-front-layout>
