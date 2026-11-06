@if ($items->isNotEmpty())
    <div class="widget popular-feeds" data-post-list="{{ $kind }}">
        <h5 class="widget-title">{{ $title }}</h5>
        <div class="popular-feed-loop">
            @foreach ($items as $item)
                <div class="single-popular-feed">
                    <div class="feed-desc">
                        <a class="feed-img" href="{{ route('front.posts.show', $item->id) }}">
                            <img src="{{ $item->images->first()?->image ? asset('storage/'.$item->images->first()->image) : asset('assets/images/product-placeholder.svg') }}" alt="{{ $item->title }}" loading="lazy">
                        </a>
                        <h6 class="post-title"><a href="{{ route('front.posts.show', $item->id) }}">{{ $item->title }}</a></h6>
                        @if ($showReads ?? false)
                            <span class="time"><i class="lni lni-eye"></i> {{ __('app.reads_count', ['count' => number_format($item->views_count)]) }}</span>
                        @else
                            <span class="time"><i class="lni lni-calendar"></i> {{ $item->created_at->format('Y-m-d') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
