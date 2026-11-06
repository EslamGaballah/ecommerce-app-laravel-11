{{-- $sections from PostController, $similarTitle names the first list. --}}
<aside class="col-lg-4 col-md-12 col-12">
    <div class="sidebar blog-grid-page">
        @include('front.blog._post-list', ['title' => $similarTitle, 'items' => $sections['similar'], 'kind' => 'similar'])
        @include('front.blog._post-list', ['title' => __('app.most_read_posts'), 'items' => $sections['mostRead'], 'kind' => 'most-read', 'showReads' => true])
        @include('front.blog._post-list', ['title' => __('app.latest_posts'), 'items' => $sections['latest'], 'kind' => 'latest'])
    </div>
</aside>
