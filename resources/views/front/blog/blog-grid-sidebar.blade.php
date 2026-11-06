 <x-front-layout :title="__('app.blog')">

        <x-slot:breadcrumb>
            <div class="breadcrumbs">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="breadcrumbs-content">
                                <h1 class="page-title">{{ __('app.posts') }}</h1>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <ul class="breadcrumb-nav">
                                <li><a href="{{ route('home') }}"><i class="lni lni-home"></i> {{ __('app.home') }}</a></li>
                                <li><a href="{{ route('front.posts.index') }}">{{ __('app.blog') }}</a></li>
                                {{-- <li>{{$post->title}}</li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:breadcrumb>

    <!-- Start Blog Singel Area -->
    <section class="section blog-section blog-list">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12 col-12">
                    <div class="row">
                        @forelse ($posts as $post)
                            <div class="col-md-6 col-12">
                            <!-- Start Single Blog -->
                            <div class="single-blog">
                                <div class="blog-img">
                                    <a href="{{ route('front.posts.show', $post->id) }}">
                                        <img src="{{ $post->images->first()?->image ? asset('storage/'.$post->images->first()->image) : asset('assets/images/product-placeholder.svg') }}" 
                                        alt="{{ $post->title }}">
                                    </a>
                                </div>
                                <div class="blog-content">
                                    @if($post->category)
                                        <a class="category" href="{{ route('dashboard.categories.show', $post->category->id) }}">{{ $post->category->name }}</a>
                                    @endif
                                    <h4>
                                        <a href="{{ route('front.posts.show', $post->id) }}">{{$post->title}}</a>
                                    </h4>
                                    <p>{{ $post->excerpt }}</p>
                                    <div class="button">
                                        <a href="{{ route('front.posts.show', $post->id) }}" class="btn">{{ __('app.read_more') }}</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Blog -->
                        </div>
                        @empty
                            {{ __('app.no_posts_yet') }}
                        @endforelse
                        
                    </div>
                    <!-- Pagination -->
                    <div class="blog-pagination">
                        {{ $posts->links('pagination::bootstrap-5') }}
                    </div>
                    <!--/ End Pagination -->
                </div>
                @include('front.blog._sidebar', ['similarTitle' => __('app.similar_to_last_read')])
            </div>
        </div>
    </section>
    <!-- End Blog Singel Area -->
</x-front-layout>

  
    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
   