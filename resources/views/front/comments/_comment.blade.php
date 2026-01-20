<li>
    <div class="comment-img">
        <img src="{{ $comment->user->image }}" alt="img">
    </div>
    <div class="comment-desc">
        <div class="desc-top">
            <h6>{{ $comment->user->name }}</h6>
            <span class="date">{{ $comment->updated_at->diffForHumans() }}</span>
            <a href="javascript:void(0)" class="reply-link"><i class="lni lni-reply"></i>Reply</a>
        </div>
        <p>{{ $comment->body }}</p>
    </div>
</li>

@if($comment->replies->count() > 0)
    <li class="children">
        <ul class="comments-list">
            @foreach($comment->replies as $reply)
                @include('partials._comment', ['comment' => $reply])
            @endforeach
        </ul>
    </li>
@endif