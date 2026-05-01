{{-- <li class="comment-item" data-id="{{ $comment->id }}">
    <div class="comment-desc">
        <h6 class="user-name">{{ $comment->user->name }}</h6>
        <p class="comment-body-text" data-id="{{ $comment->id }}">{{ $comment->body }}</p>
        
        <div class="actions">
            <button  type="button" class="btn-reply" data-id="{{ $comment->id }}">{{__('app.reply')}}</button>
            @can('update', $comment)
                <button  type="button" class="btn-edit" data-id="{{ $comment->id }}">{{__('app.edit')}}</button>
                <button type="button" class="btn-delete" data-id="{{ $comment->id }}">{{__('app.delete')}}</button>
            @endcan
        </div>
    </div>
    
    <ul class="replies-container" data-id="{{ $comment->id }}">
        @foreach($comment->replies as $reply)
            @include('partials._comment', ['comment' => $reply])
        @endforeach
    </ul>
</li> --}}