 <li>
    <div class="comment-img">
        <img src="{{ $comment->user->image ?? 'https://via.placeholder.com/50' }}" alt="img">
    </div>
    <div class="comment-desc">
        <div class="desc-top">
            <h6>{{ $comment->user->name }}</h6>
            <span class="date">{{ $comment->updated_at->diffForHumans() }}</span>
            <!-- reply button-->
            <button type="button" 
                    onclick="event.preventDefault(); document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('d-none')" 
                    class="reply-link" 
                    style="background:none; border:none; color:#081828; cursor:pointer;">
                <i class="lni lni-reply"></i> {{ __('app.reply') }}
            </button>

            @if(auth()->check() && auth()->id() == $comment->user_id)
                <!-- edit comment button-->
                <button type="button" 
                    onclick="event.preventDefault(); document.getElementById('edit-form-{{ $comment->id }}').classList.toggle('d-none')" 
                    class=" edit text-primary">
                    <i class="lni lni-write"></i> {{ __('app.edit') }}
                </button>

                <!-- Delete comment -->
                <form action="{{ route('comments.destroy', $comment->id) }}" 
                    method="POST" 
                    style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class=" delete text-danger" onclick="return confirm('هل أنت متأكد؟')" 
                    style="border:none; background:none;">
                        <i class="lni lni-trash"></i> {{ __('app.delete') }}
                    </button>
                </form>
            @endif

        </div>
        <p id="body-{{ $comment->id }}" >{{ $comment->body }}</p>

            <!-- edit comment form-->
        <div id="edit-form-{{ $comment->id }}" class="comment-form d-none mt-2"
            style="margin-top: 10px; padding-left: 20px;">
            <h6 class="mb-2"> {{ __('app.edit') }}</h6>
            <form action="{{ route('comments.update', $comment->id) }}" 
                method="POST">
                @csrf
                @method('PUT')
                <div class="form-box form-group">
                <textarea 
                    name="body" 
                    class="form-control form-control-custom">{{ $comment->body }}
                </textarea>
                </div>
                <button 
                    type="submit" 
                    class="btn btn-sm btn-primary">{{ __('app.save_changes') }}
                </button>
                <button 
                    type="button" 
                    onclick="document.getElementById('edit-form-{{ $comment->id }}').classList.add('d-none')" 
                                class="btn btn-sm btn-secondary">{{ __('app.cancel') }}</button>
            </form>
        </div>
    </div>
            <!-- reply form-->
    <div id="reply-form-{{ $comment->id }}" class="comment-form d-none " 
        style="margin-top: 10px; padding-left: 20px;">
        <h6 class="mb-2">Reply to {{ $comment->user->name }}</h6>
        <form action="{{ route('comments.store', $comment->post_id) }}" 
            method="POST">
            @csrf
            <input type="hidden" name="post_id" value="{{ $comment->post_id }}">
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <div class="row">
                <div class="col-12">
                    <div class="form-box form-group">
                        <textarea name="body" class="form-control form-control-custom" 
                                  placeholder="{{ __('app.write_your_reply') }}..." required>
                        </textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="button">
                        <button type="submit" class="btn btn-sm">{{ __('app.submit_reply') }}</button>
                        <button type="button" onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.add('d-none')" 
                                class="btn btn-sm btn-secondary">{{ __('app.cancel') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</li>


@if($comment->replies->count() > 0)
   
    <li class="children" >
        <ul class="comments-list">
            @foreach($comment->replies as $reply)
                @include('partials._comment', [
                    'comment' => $reply,
                    ])
            @endforeach
        </ul>
    </li>
    
@endif

