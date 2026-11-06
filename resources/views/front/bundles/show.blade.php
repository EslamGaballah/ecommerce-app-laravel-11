<x-front-layout :title="$bundle->name">
    @push('page-styles')
        <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    @endpush

    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => $bundle->name])
    </x-slot:breadcrumb>

    <section class="home-section home-section-tinted">
        <div class="container">
            <div class="bundle-hero">
                <div>
                    @if ($bundle->description)
                        <p>{{ $bundle->description }}</p>
                    @endif
                    <ul class="bundle-tiers">
                        @foreach ($bundle->tiers as $tier)
                            <li>{{ __('app.bundle_tier_line', ['count' => $tier->min_quantity, 'discount' => $tier->label]) }}</li>
                        @endforeach
                    </ul>
                    @if ($bundle->distinct_products)
                        <small class="text-muted d-block mt-2">{{ __('app.bundle_distinct_note') }}</small>
                    @endif
                </div>
                <div id="bundle-progress" class="bundle-progress" data-url="{{ route('bundles.show', $bundle) }}" aria-live="polite">
                    @include('front.bundles._progress')
                </div>
            </div>

            <div class="row home-products mt-4">
                @foreach ($bundle->products as $product)
                    <div class="col-lg-3 col-md-4 col-6">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-3">
                <div class="button"><a href="{{ route('cart.index') }}" class="btn">{{ __('app.view_cart') }}</a></div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('cart:updated', function () {
                const box = document.getElementById('bundle-progress');

                fetch(box.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => { box.innerHTML = html; });
            });
        </script>
    @endpush
</x-front-layout>
