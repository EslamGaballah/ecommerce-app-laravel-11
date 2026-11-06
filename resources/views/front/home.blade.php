<x-front-layout>
    @push('page-styles')
        <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    @endpush

    <!-- Start Hero -->
    <section class="home-hero">
        <div class="container">
            <x-alert type="info" />

            <div class="row g-3 g-lg-4">
                @php($mainDeal = $heroProducts->first())
                <div class="{{ $heroProducts->count() > 1 ? 'col-lg-8' : '' }} col-12">
                    <div class="home-banner home-banner-main">
                        <div class="home-banner-text">
                            @if ($mainDeal)
                                <span class="home-badge">{{ __('app.save_percent', ['percent' => $mainDeal->discount_percent]) }}</span>
                                <h1>{{ $mainDeal->name }}</h1>
                                <p class="home-price">
                                    {{ Currency::format($mainDeal->final_price) }}
                                    <del>{{ Currency::format($mainDeal->final_compare_price) }}</del>
                                </p>
                                <div class="home-banner-actions">
                                    <a href="{{ route('products.show', $mainDeal->slug) }}" class="btn home-btn">{{ __('app.shop_now') }}</a>
                                    <a href="{{ route('products.index') }}" class="home-link">{{ __('app.view_all') }} <i class="lni lni-arrow-right"></i></a>
                                </div>
                            @else
                                <h1>{{ __('app.welcome_title') }}</h1>
                                <p>{{ __('app.welcome_text') }}</p>
                                <a href="{{ route('products.index') }}" class="btn home-btn">{{ __('app.shop_now') }}</a>
                            @endif
                        </div>
                        @if ($mainDeal)
                            <img src="{{ $mainDeal->image_url }}" alt="{{ $mainDeal->name }}">
                        @endif
                    </div>
                </div>

                @if ($heroProducts->count() > 1)
                    <div class="col-lg-4 col-12">
                        <div class="home-side-banners">
                            @foreach ($heroProducts->slice(1) as $deal)
                                <a href="{{ route('products.show', $deal->slug) }}" class="home-banner home-banner-small">
                                    <div class="home-banner-text">
                                        <span class="home-badge">{{ __('app.save_percent', ['percent' => $deal->discount_percent]) }}</span>
                                        <h2>{{ $deal->name }}</h2>
                                        <p class="home-price">{{ Currency::format($deal->final_price) }}</p>
                                    </div>
                                    <img src="{{ $deal->image_url }}" alt="{{ $deal->name }}">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    <!-- End Hero -->

    <!-- Start Benefits -->
    <section class="home-benefits">
        <div class="container">
            <ul>
                @foreach (App\Enums\PaymentMethod::enabled() as $method)
                    <li>
                        <i class="lni {{ $method === App\Enums\PaymentMethod::CASH ? 'lni-wallet' : 'lni-credit-cards' }}"></i>
                        <div>
                            <h5>{{ $method->label() }}</h5>
                            <span>{{ __('app.payment_'.$method->value.'_text') }}</span>
                        </div>
                    </li>
                @endforeach
                <li>
                    <i class="lni lni-delivery"></i>
                    <div>
                        <h5>{{ __('app.delivery_governorates') }}</h5>
                        <span>{{ __('app.delivery_governorates_text') }}</span>
                    </div>
                </li>
                <li>
                    <i class="lni lni-package"></i>
                    <div>
                        <h5>{{ __('app.order_tracking') }}</h5>
                        <span>{{ __('app.order_tracking_text') }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <!-- End Benefits -->

    <!-- Start Categories -->
    @if ($homeCategories->isNotEmpty())
        <section class="home-section home-categories">
            <div class="container">
                <div class="home-section-head">
                    <div>
                        <h2>{{ __('app.shop_by_category') }}</h2>
                        <p>{{ __('app.shop_by_category_text') }}</p>
                    </div>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach ($homeCategories as $category)
                        <div class="col-lg-{{ $homeCategories->count() === 2 ? 6 : 4 }} col-md-6 col-12">
                            <div class="home-category">
                                <div class="home-category-text">
                                    <h3><a href="{{ route('products.index', ['category' => $category->id]) }}">{{ $category->name }}</a></h3>
                                    <span class="home-category-count">{{ __('app.products_count_short', ['count' => $category->active_products_count]) }}</span>
                                    <ul>
                                        @foreach ($category->childrenRecursive->take(3) as $child)
                                            <li><a href="{{ route('products.index', ['category' => $child->id]) }}">{{ $child->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                                @if ($category->cover_image)
                                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="home-category-image">
                                        <img src="{{ $category->cover_image }}" alt="{{ $category->name }}" loading="lazy">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- End Categories -->

        @include('front.partials.product-section', [
        'products' => $featured,
        'title' => __('app.featured_products'),
        'text' => __('app.featured_products_text'),
        'link' => route('products.index', ['featured' => 1]),
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
        'products' => $bestSellers,
        'title' => __('app.best_sellers'),
        'text' => __('app.best_sellers_text'),
        'link' => route('products.index', ['sort_by' => 'best_selling']),
        'tinted' => true,
    ])

    <!-- Start New Arrivals -->
    <section class="home-section home-section-tinted trending-product">
        <div class="container">
            <div class="home-section-head">
                <div>
                    <h2>{{ __('app.new_arrivals') }}</h2>
                    <p>{{ __('app.new_arrivals_text') }}</p>
                </div>
                @if ($products->isNotEmpty())
                    <a href="{{ route('products.index') }}" class="home-link">{{ __('app.view_all') }} <i class="lni lni-arrow-right"></i></a>
                @endif
            </div>
            <div class="row home-products">
                @forelse ($products as $product)
                    <div class="col-lg-3 col-md-4 col-6">
                        <x-product-card :product="$product" />
                    </div>
                @empty
                    <p class="text-center">{{ __('app.welcome_text') }}</p>
                @endforelse
            </div>
        </div>
    </section>
        <!-- End New Arrivals -->

    @include('front.partials.product-section', [
        'products' => $mostViewed,
        'title' => __('app.most_viewed'),
        'text' => __('app.most_viewed_text'),
        'link' => route('products.index', ['sort_by' => 'most_viewed']),
    ])

    <!-- Start Special Offers -->
    @if ($offers->isNotEmpty())
        <section class="home-section home-offers">
            <div class="container">
                <div class="home-section-head">
                    <div>
                        <h2>{{ __('app.special_offers') }}</h2>
                        <p>{{ __('app.special_offers_text') }}</p>
                    </div>
                </div>
                <div class="row home-products">
                    @foreach ($offers as $product)
                        <div class="col-lg-3 col-md-4 col-6">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- End Special Offers -->

    <!-- Start Brands -->
    @if ($brands->isNotEmpty())
        <section class="home-brands">
            <div class="container">
                <h2>{{ __('app.popular_brands') }}</h2>
                <ul>
                    @foreach ($brands as $brand)
                        <li><a href="{{ route('products.index', ['brand' => [$brand->id]]) }}">{{ $brand->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
    <!-- End Brands -->

    <!-- Start Blog -->
    @if ($posts->isNotEmpty())
        <section class="home-section home-section-tinted blog-section">
            <div class="container">
                <div class="home-section-head">
                    <div>
                        <h2>{{ __('app.our_latest_news') }}</h2>
                        <p>{{ __('app.latest_news_text') }}</p>
                    </div>
                    <a href="{{ route('front.posts.index') }}" class="home-link">{{ __('app.view_all') }} <i class="lni lni-arrow-right"></i></a>
                </div>
                <div class="row home-posts">
                    @foreach ($posts as $post)
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="single-blog">
                                @if ($post->images->isNotEmpty())
                                    <div class="blog-img">
                                        <a href="{{ route('front.posts.show', $post->id) }}">
                                            <img src="{{ asset('storage/'.$post->images->first()->image) }}" alt="{{ $post->title }}" loading="lazy">
                                        </a>
                                    </div>
                                @endif
                                <div class="blog-content">
                                    <h4><a href="{{ route('front.posts.show', $post->id) }}">{{ $post->title }}</a></h4>
                                    <p>{{ $post->excerpt }}</p>
                                    <div class="button">
                                        <a href="{{ route('front.posts.show', $post->id) }}" class="btn">{{ __('app.read_more') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- End Blog -->
</x-front-layout>
