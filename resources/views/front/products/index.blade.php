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
                            {{-- <div class="single-widget search">
                                <h3>{{ __('app.search') }}</h3>
                                <form action="#">
                                    <input type="text" placeholder="{{ __('app.search') }}...">
                                    <button type="submit"><i class="lni lni-search-alt"></i></button>
                                </form>
                            </div> --}}
                            <!-- End Single Widget -->
                            <!-- Start Single Widget -->
                            <div class="single-widget">
                                <h3>{{ __('app.categories') }}</h3>
                                  @foreach($categories as $category)
                                    <div class="form-check">
                                        <input class="form-check-input filter-input" 
                                            type="checkbox" 
                                            name="category_id[]" 
                                            value="{{ $category->id }}" 
                                            id="cat-{{ $category->id }}">
                                        <label class="form-check-label" for="cat-{{ $category->id }}">{{ $category->name }}
                                            <span>({{ $category->products_count }})</span>
                                        </label>
                                    </div>
                                @endforeach  

                                <!-- End Single Widget -->
                            </div>
                            <!-- End Single Widget -->
                            <!-- Start Single Widget -->
                            {{-- <div class="single-widget range">
                                <h3>Price Range</h3>
                                <input type="range" class="form-range" name="range" step="1" min="100" max="10000"
                                    value="10" onchange="rangePrimary.value=value">
                                <div class="range-inner">
                                    <label>$</label>
                                    <input type="text" id="rangePrimary" placeholder="100" />
                                </div>
                            </div> --}}
                            <!-- End Single Widget -->
                            <!-- Start Single Widget -->
                            <div class="single-widget condition">
                                <h3>Filter by Price</h3>
                                {{-- <div class="form-check"> --}}
                                    <input class="form-control filter-input" type="number"
                                        name= "min_price" 
                                        value="" 
                                        placeholder="أقل سعر"
                                        >
                                    {{-- <label class="form-check-label" for="flexCheckDefault1">
                                        $50 - $100L (208)
                                    </label> --}}
                                {{-- </div> --}}
                                {{-- <div class="form-check"> --}}
                                    <input class="form-control filter-input" type="number"
                                        name= "max_price" 
                                        value="" 
                                        placeholder="اعلى سعر"
                                        >
                                    {{-- <label class="form-check-label" for="flexCheckDefault1">
                                        $50 - $100L (208)
                                    </label> --}}
                                {{-- </div> --}}
                                {{-- <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault2">
                                    <label class="form-check-label" for="flexCheckDefault2">
                                        $100L - $500 (311)
                                    </label>
                                </div> --}}
                            </div>
                            <!-- End Single Widget -->
                            <!-- Start Single Widget -->
                            {{-- <div class="single-widget condition">
                                <h3>Filter by Brand</h3>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault11">
                                    <label class="form-check-label" for="flexCheckDefault11">
                                        Apple (254)
                                    </label>
                                </div>
                                
                            </div> --}}
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
                                            <label for="sorting">Sort by:</label>
                                            <select class="form-control filter-input" id="sorting" name="sort_by"
                                                 {{-- onchange="this.form.submit()" --}}
                                                 >
                                                <option value="">Default</option>
                                                <option value="low_price" @selected(request('sort') == 'low_price')> Lower Price</option>
                                                <option value="high_price" @selected(request('sort') == 'high_price')> higher Price</option>
                                                <option value="rating" @selected(request('sort') == 'rating')>Average Rating</option>
                                                <option value="newest" @selected(request('sort') == 'newest')>Newest</option>
                                                <option value="oldest" @selected(request('sort') == 'oldest')></option>
                                            </select>
                                            <h3 class="total-show-product">Showing: 
                                                <span>{{ $products->firstItem() }} to {{ $products->lastItem() }}
                                                of {{ $products->total() }} items
                                                </span></h3>
                                        </form>

                                       
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-4 col-12">
                                    <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                            <button class="nav-link active" id="nav-grid-tab" data-bs-toggle="tab"
                                                data-bs-target="#nav-grid" type="button" role="tab"
                                                aria-controls="nav-grid" aria-selected="true"><i
                                                    class="lni lni-grid-alt"></i></button>
                                            <button class="nav-link" id="nav-list-tab" data-bs-toggle="tab"
                                                data-bs-target="#nav-list" type="button" role="tab"
                                                aria-controls="nav-list" aria-selected="false"><i
                                                    class="lni lni-list"></i></button>
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
                                             <p>No Products! </p>
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

    
    @push('style')
<style>
.pagination {
    display: flex !important;
    justify-content: center;
}
</style>
@endpush



@push('script')
<script>
    function fetchProducts(url = null) {
        let categories = [];
        $('input[name="category_id[]"]:checked').each(function() {
            categories.push($(this).val());
        });

        let data = {
            category_id: categories,
            sort_by: $('select[name="sort_by"]').val(),
            min_price: $('input[name="min_price"]').val(),
            max_price: $('input[name="max_price"]').val(),
        };

        let fetchUrl = url ? url : "{{ route('products.index') }}";

        $.ajax({
            url: fetchUrl,
            data: data,
            beforeSend: function() {
                $('#products-container').css('opacity', '0.5'); // تأثير بصري أثناء التحميل
            },
            success: function(response) {
                $('#products-container').html(response);
                $('#products-container').css('opacity', '1');
                
                // تحديث الرابط في المتصفح
                window.history.pushState({}, '', fetchUrl + (fetchUrl.includes('?') ? '&' : '?') + $.param(data));
                
                // العودة للأعلى بسلاسة عند الانتقال لصفحة جديدة
                if(url) {
                    $('html, body').animate({scrollTop: $(".product-grids").offset().top - 50}, 500);
                }
            }
        });
    }

    // مراقبة التغيير في المدخلات
    $(document).on('change', '.filter-input', function() {
        fetchProducts();
    });

    // إصلاح الضغط على الروابط (تم حذف التكرار)
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url && url !== '#') {
            fetchProducts(url);
        }
    });

    // مراقبة حقول السعر
    let timer;
    $(document).on('keyup', 'input.filter-input', function() {
        clearTimeout(timer);
        timer = setTimeout(fetchProducts, 500); 
    });
</script>
@endpush

</x-front-layout>

