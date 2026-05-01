@foreach($comments as $comment)
<div class="comment" data-id="{{ $comment->id }}" style="margin-left: {{ $level ?? 0 }}px">

    <strong>{{ $comment->user->name }}</strong>

    <p class="comment-body" data-id="{{ $comment->id }}">
        {{ $comment->body }}
    </p>

    <button class="btn-reply" data-id="{{ $comment->id }}">Reply</button>

    @can('update', $comment)
        <button class="btn-edit" data-id="{{ $comment->id }}">Edit</button>
        <button class="btn-delete" data-id="{{ $comment->id }}">Delete</button>
    @endcan

    <div class="replies"></div>

    @if($comment->replies->count())
        @include('comments', [
            'comments' => $comment->replies,
            'level' => ($level ?? 0) + 30
        ])
    @endif
</div>
@endforeach


{{-- add comment --}}

<form id="add-comment-form">
    @csrf
    <textarea id="new-comment-body"></textarea>
    <button>Add Comment</button>
</form>

<div id="comments-wrapper">
    @include('comments.comments', ['comments' => $comments])
</div>

@push('script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
// ADD ROOT COMMENT
$('#add-comment-form').submit(function(e){
    e.preventDefault();

    $.post("{{ route('comments.store') }}", {
        body: $('#new-comment-body').val(),
        _token: '{{ csrf_token() }}'
    }, function(res){
        location.reload(); // أول مرة فقط
    });
});

// REPLY INLINE
$(document).on('click', '.btn-reply', function(){
    let id = $(this).data('id');

    $(this).after(`
        <form class="reply-form">
            <textarea></textarea>
            <input type="hidden" value="${id}">
            <button>Reply</button>
        </form>
    `);
});

// SUBMIT REPLY
$(document).on('submit', '.reply-form', function(e){
    e.preventDefault();
    let body = $(this).find('textarea').val();
    let parent = $(this).find('input').val();
    let container = $(this).closest('.comment').find('.replies:first');

    $.post("{{ route('comments.store') }}", {
        body, parent_id: parent, _token: '{{ csrf_token() }}'
    }, function(res){
        container.append(`<div style="margin-left:30px">${res.user}: ${res.body}</div>`);
    });

    $(this).remove();
});

// EDIT
$(document).on('click', '.btn-edit', function(){
    let id = $(this).data('id');
    let p = $(`.comment-body[data-id="${id}"]`);
    let text = p.text();

    p.html(`
        <textarea class="edit">${text}</textarea>
        <button class="save" data-id="${id}">Save</button>
    `);
});

// UPDATE
$(document).on('click', '.save', function(){
    let id = $(this).data('id');
    let body = $(this).prev().val();
    let p = $(`.comment-body[data-id="${id}"]`);

    $.ajax({
        url:`/comments/${id}`,
        type:'PUT',
        data:{ body, _token:'{{ csrf_token() }}' },
        success:()=>p.text(body)
    });
});

// DELETE
$(document).on('click', '.btn-delete', function(){
    let id = $(this).data('id');

    if(!confirm('Delete comment?')) return;

    $.ajax({
        url:`/comments/${id}`,
        type:'DELETE',
        data:{ _token:'{{ csrf_token() }}' },
        success:()=> $(`.comment[data-id="${id}"]`).remove()
    });
});
</script>

@endpush