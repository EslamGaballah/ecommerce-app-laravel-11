<x-front-layout >

    <x-slot:breadcrumb>
        <div class="breadcrumbs">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="breadcrumbs-content">
                            <h1 class="page-title">{{ __('app.products')  }}</h1>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <ul class="breadcrumb-nav">
                            <li><a href="{{ route('home') }}"><i class="lni lni-home"></i> {{ __('app.home') }}</a></li>
                            <li><a href="{{ route('products.index') }}">{{ __('app.shop') }}</a></li>
                            <li>{{ __('app.products')  }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:breadcrumb>
       

    <!-- Start Product Grids -->
    <section class="product-grids section">
        <div class="container">
            <div class="row">

                    <!----------------- Start Product Sidebar ------------------->
                <aside class="col-lg-3 col-12">
                    <div class="filter-sidebar">
                        <div class="product-sidebar">
                            <!-- Start Single Widget -->
                            <div class="single-widget search">
                                <h3>{{ __('app.search') }}</h3>
                                <form action="{{ request()->url() }}" method="get" >
                                    <input type="text" name="search"  placeholder="{{ __('app.search') }}...">
                                    {{-- <button type="submit"><i class="lni lni-search-alt"></i></button> --}}
                                </form>
                            </div>
                            <!-- End Single Widget -->
                             <!-- Start Single Widget -->
                            <div class="single-widget condition">
                               <h3>{{ __('app.brand') }}</h3>
                                  @foreach($brands as $brand)
                                    <div class="form-check">
                                        <input class="form-check-input filter-input" 
                                            type="checkbox" 
                                            name="brand[]" 
                                            value="{{ $brand->id }}" 
                                            id="brand-{{ $brand->id }}"
                                            @checked(in_array($brand->id, (array) request('brand', [])))>
                                        <label class="form-check-label" for="brand-{{ $brand->id }}">{{ $brand->name }}
                                            <span>({{ $brand->products_count }})</span>
                                        </label>
                                    </div>
                                @endforeach  

                            </div>
                            <!-- End Single Widget -->
                            <!-- Start Single Widget -->
                            <div class="single-widget">
                                <h3>{{ __('app.categories') }}</h3>
                                  @foreach($categories as $category)
                                    <div class="form-check">
                                        <input class="form-check-input filter-input" 
                                            type="checkbox" 
                                            name="category[]" 
                                            value="{{ $category->id }}" 
                                            id="cat-{{ $category->id }}"
                                            @checked(in_array($category->id, (array) request('category', [])))
                                            >
                                        <label class="form-check-label" for="cat-{{ $category->id }}">{{ $category->name }}
                                            <span>({{ $category->products_count }})</span>
                                        </label>
                                    </div>
                                @endforeach  

                                <!-- End Single Widget -->
                            </div>
                            <!-- Start Single Widget -->
                            <div class="single-widget condition">
                                <h3>{{ __('app.filter_by_price') }}</h3>
                                <div class="form-check">
                                    <input class="form-control filter-input"
                                         type="number"
                                        name= "min_price" 
                                        value="{{ request('min_price') }}" 
                                        placeholder="{{ __('app.min_price') }}"
                                        >
                                 
                                </div>
                                <div class="form-check">
                                    <input class="form-control filter-input"
                                        type="number"
                                        name= "max_price" 
                                        value="{{ request('max_price') }}" 
                                        placeholder="{{ __('app.max_price') }}"
                                        >
                                </div>
                                <!-- End Single Widget -->
                           
                        </div>
                        <!-- End Product Sidebar -->
                    </div>
                </aside>

                    <!-- Start Products area -->
                <div class="col-lg-9 col-12">
                    <div class="product-grids-head">
                        <div class="product-grid-topbar">
                            <div class="row align-items-center">
                                <div class="col-lg-7 col-md-8 col-12">
                                    <div class="product-sorting">

                                       <form method="GET" id="sortingForm">
                                            <label for="sorting">{{ __('app.sort_by') }}:</label>
                                            <select class="form-control filter-input" id="sorting" name="sort_by"
                                                 {{-- onchange="this.form.submit()" --}}
                                                 >
                                                <option value="">{{ __('app.default') }}</option>
                                                <option value="low_price" @selected(request('sort_by') == 'low_price')>{{ __('app.low_price') }}</option>
                                                <option value="high_price" @selected(request('sort_by') == 'high_price')>{{ __('app.high_price') }}</option>
@if (Setting::get('product_ratings_visible'))
                                                <option value="rating" @selected(request('sort_by') == 'rating')>{{ __('app.average_rating') }}</option>
@endif
                                                <option value="best_selling" @selected(request('sort_by') == 'best_selling')>{{ __('app.best_sellers') }}</option>
                                                <option value="most_viewed" @selected(request('sort_by') == 'most_viewed')>{{ __('app.most_viewed') }}</option>
                                                <option value="newest"  @selected(request('sort_by') == 'newest')>{{ __('app.newest') }}</option>
                                                <option value="oldest" @selected(request('sort_by') == 'oldest')>{{ __('app.oldest') }}</option>
                                            </select>
                                            <label for="per-page">{{ __('app.per_page') }}:</label>
                                            <select class="form-control filter-input per-page-select" id="per-page" name="per_page">
                                                @foreach ($perPageOptions as $option)
                                                    <option value="{{ $option }}" @selected($option === $perPage)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                            <h3 class="total-show-product">{{ __('app.showing') }}:
                                                <span id="products-range-text">{{ __('app.showing_range', ['first' => $products->firstItem() ?? 0, 'last' => $products->lastItem() ?? 0, 'total' => $products->total()]) }}</span></h3>
                                        </form>

                                       
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-4 col-12">
                                    <nav aria-label="{{ __('app.view_as') }}">
                                        <div class="nav nav-tabs" id="nav-tab">
                                            <button class="nav-link active" type="button" data-view="grid" aria-pressed="true" title="{{ __('app.view_grid') }}" aria-label="{{ __('app.view_grid') }}"><i class="lni lni-grid-alt"></i></button>
                                            <button class="nav-link" type="button" data-view="list" aria-pressed="false" title="{{ __('app.view_list') }}" aria-label="{{ __('app.view_list') }}"><i class="lni lni-list"></i></button>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-grid" role="tabpanel"
                                aria-labelledby="nav-grid-tab">
                                {{-- <div class="row"> --}}

                                    <div id="products-container">
                                        @include('front.products._list')
                                    </div>

                                    {{-- @forelse ($products as $product)
                                        <div class="col-lg-4 col-md-6 col-12">
                                            <!-- Start Single Product -->
                                            <x-product-card :product="$product" />
                                            <!-- End Single Product -->
                                        </div>
                                    @empty
                                             <p>{{ __('app.no_products') }}</p>
                                    @endforelse --}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Product Grids -->

   

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    
@push('script')
<script>
let timer = null;
let isLoading = false;

// The sidebar and sort forms. The header has its own search box, and its own forms must keep working.
const FILTERS = '.product-grids';

/* =========================
   BUILD DATA
========================= */
function getFiltersData() {
    const params = new URLSearchParams(window.location.search);

    return {
        category: $(FILTERS + ' input[name="category[]"]:checked').map(function () {
            return $(this).val();
        }).get(),

        brand: $(FILTERS + ' input[name="brand[]"]:checked').map(function () {
            return $(this).val();
        }).get(),

        sort_by: $(FILTERS + ' select[name="sort_by"]').val(),
        per_page: $(FILTERS + ' select[name="per_page"]').val(),
        min_price: $(FILTERS + ' input[name="min_price"]').val(),
        max_price: $(FILTERS + ' input[name="max_price"]').val(),
        search: $(FILTERS + ' input[name="search"]').val(),
        // Arrived by a link with no control on this page, such as Our Picks.
        featured: params.get('featured') || '',
    };
}

/* =========================
   UPDATE URL
========================= */
function updateUrl(data, page) {
    const url = new URL("{{ route('products.index') }}");

    Object.keys(data).forEach(key => {
        if (Array.isArray(data[key])) {
            data[key].forEach(val => {
                url.searchParams.append(key + '[]', val);
            });
        } else if (data[key]) {
            url.searchParams.set(key, data[key]);
        }
    });

    if (page && page !== '1') {
        url.searchParams.set('page', page);
    }

    window.history.pushState({}, '', url.toString());
}

/* =========================
   RESTORE INPUTS FROM URL
========================= */
// Links use category=1, the checkboxes category[]=1, and the home page brand[0]=1.
function valuesFromUrl(params, name) {
    return [...params.entries()]
        .filter(([key]) => key === name || key === name + '[]' || key.startsWith(name + '['))
        .map(([, value]) => value);
}

function restoreInputsFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const categories = valuesFromUrl(params, 'category');
    const brands = valuesFromUrl(params, 'brand');

    // checkboxes
    $(FILTERS + ' input[name="category[]"]').each(function () {
        $(this).prop('checked', categories.includes($(this).val()));
    });

    $(FILTERS + ' input[name="brand[]"]').each(function () {
        $(this).prop('checked', brands.includes($(this).val()));
    });

    // inputs
    $(FILTERS + ' input[name="min_price"]').val(params.get('min_price') || '');
    $(FILTERS + ' input[name="max_price"]').val(params.get('max_price') || '');
    $(FILTERS + ' input[name="search"]').val(params.get('search') || '');
    $(FILTERS + ' select[name="sort_by"]').val(params.get('sort_by') || '');

    if (params.get('per_page')) {
        $(FILTERS + ' select[name="per_page"]').val(params.get('per_page'));
    }
}

/* =========================
   FETCH PRODUCTS
========================= */
function fetchProducts(url = null, remember = true) {

    if (isLoading) return;

    let data = getFiltersData();
    let fetchUrl = url ? url : "{{ route('products.index') }}";

    isLoading = true;

    $.ajax({
        url: fetchUrl,
        data: data,

        beforeSend: function () {
            $('#products-container').css('opacity', '0.5');
        },

        success: function (response) {
            $('#products-container').html(response);
            $('#products-container').css('opacity', '1');
            $('#products-range-text').text($('#products-range').text());

            // Going back or forward is already in the browser's history.
            if (remember) {
                updateUrl(data, url ? new URL(url, window.location.href).searchParams.get('page') : null);
            }

            if (url) {
                $('html, body').animate({
                    scrollTop: $(".product-grids").offset().top - 50
                }, 400);
            }
        },

        complete: function () {
            isLoading = false;
        }
    });
}

/* =========================
   GRID OR LIST
========================= */
function showView(view) {
    const isList = view === 'list';

    $('#products-container').toggleClass('view-list', isList);

    $('[data-view]').each(function () {
        const active = $(this).data('view') === view;

        $(this).toggleClass('active', active).attr('aria-pressed', active ? 'true' : 'false');
    });
}

function savedView() {
    try {
        return localStorage.getItem('products-view') || 'grid';
    } catch (error) {
        return 'grid';
    }
}

$(document).on('click', '[data-view]', function () {
    const view = $(this).data('view');

    showView(view);

    try {
        localStorage.setItem('products-view', view);
    } catch (error) {}
});

/* =========================
   EVENTS
========================= */

// change filters
$(document).on('change', FILTERS + ' .filter-input', function () {
    fetchProducts();
});

// search (debounce)
$(document).on('input', FILTERS + ' input[name="search"]', function () {
    clearTimeout(timer);
    timer = setTimeout(fetchProducts, 500);
});

// pagination
$(document).on('click', '#products-container .pagination a', function (e) {
    e.preventDefault();

    let url = $(this).attr('href');

    if (url && url !== '#') {
        fetchProducts(url);
    }
});

// The filters update the list in place.
$(document).on('submit', FILTERS + ' form', function (e) {
    e.preventDefault();
});

/* =========================
   BACK BUTTON SUPPORT
========================= */
window.addEventListener('popstate', function () {
    restoreInputsFromUrl();
    fetchProducts(window.location.href, false);
});

/* =========================
   INIT
========================= */
$(document).ready(function () {
    restoreInputsFromUrl();
    showView(savedView());
});
</script>
@endpush

</x-front-layout>

