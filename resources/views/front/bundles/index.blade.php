<x-front-layout :title="__('app.bundles')">
    @push('page-styles')
        <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    @endpush

    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => __('app.bundles')])
    </x-slot:breadcrumb>

    <section class="home-section home-section-tinted">
        <div class="container">
            @if ($bundles->isEmpty())
                <div class="info-page-card text-center">
                    <p>{{ __('app.no_bundles') }}</p>
                    <div class="button"><a href="{{ route('products.index') }}" class="btn">{{ __('app.shop_now') }}</a></div>
                </div>
            @else
                <div class="row g-3 g-lg-4">
                    @foreach ($bundles as $bundle)
                        <div class="col-lg-4 col-md-6 col-12">
                            @include('front.bundles._card')
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-front-layout>
