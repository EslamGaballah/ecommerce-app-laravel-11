@if ($products->isNotEmpty())
    <section @class(['home-section', 'home-section-tinted' => $tinted ?? false])>
        <div class="container">
            <div class="home-section-head">
                <div>
                    <h2>{{ $title }}</h2>
                    <p>{{ $text }}</p>
                </div>
                @isset($link)
                    <a href="{{ $link }}" class="home-link">{{ __('app.view_all') }} <i class="lni lni-arrow-right"></i></a>
                @endisset
            </div>
            <div class="row home-products">
                @foreach ($products as $product)
                    <div class="col-lg-3 col-md-4 col-6">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
