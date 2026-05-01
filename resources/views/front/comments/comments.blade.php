{{-- حاوية التعليقات
<div id="comments-wrapper">
    <ul class="comments-list" style="list-style: none; padding: 0;">
        @foreach($comments as $comment)
            @include('partials._comments', ['comment' => $comment])
        @endforeach
    </ul>
</div>

<hr>

{{-- فورم إضافة تعليق جديد --}}
<form id="add-comment-form">
    @csrf
    <input type="hidden" id="post_id" value="{{ $post->id }}">

    <textarea id="new-comment-body" class="form-control"
        placeholder="{{ __('app.write_comment') }}"></textarea>

    <button type="submit" class="btn btn-primary mt-2">
        {{ __('app.add_comment') }}
    </button>
</form>

@push('style')
<style>
    .replies-container {
        list-style: none;
        margin-right: 30px; /* للـ RTL */
        border-right: 2px solid #eee;
        padding-right: 15px;
        margin-top: 10px;
    }
    .comment-item {
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 8px;
    }
    .actions { margin-top: 10px; }
    .actions button, .actions a { margin-left: 10px; cursor: pointer; border: none; background: none; color: #007bff; }
    .reply-form { margin-top: 15px; background: #f8f9fa; padding: 10px; border-radius: 5px; }
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. إضافة تعليق رئيسي (Root Comment)
    const addForm = document.getElementById('add-comment-form');
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const textarea = document.getElementById('new-comment-body');
            const wrapper = document.querySelector('.comments-list');

            const response = await fetch("{{ route('comments.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ 
                    body: textarea.value,
                     post_id: document.getElementById('post_id').value
                 })
            });

            if (response.ok) {
                const data = await response.json();
                wrapper.insertAdjacentHTML('afterbegin', data.html);
                textarea.value = '';
            }
        });
    }

    // 2. مستمع أحداث للنقرات (Reply, Edit, Delete, Cancel)
    document.addEventListener('click', async (e) => {
        const target = e.target;

        // إظهار فورم الرد
        if (target.classList.contains('btn-reply')) {
            const id = target.dataset.id;
            if (document.querySelector(`.reply-form[data-id="${id}"]`)) return;

            const formHtml = `
                <form class="reply-form" data-id="${id}">
                    <textarea class="form-control" required placeholder="اكتب ردك..."></textarea>
                    <button type="submit" class="btn btn-sm btn-primary mt-2">إرسال</button>
                    <button type="button" class="btn-cancel btn btn-sm btn-link mt-2">إلغاء</button>
                </form>`;
            target.closest('.comment-desc').insertAdjacentHTML('beforeend', formHtml);
        }

        // إلغاء (فورم الرد أو التعديل)
        if (target.classList.contains('btn-cancel')) {
            target.closest('form').remove();
        }

        // حذف تعليق
        // document.addEventListener('click', async (e) => {
        if (target.classList.contains('btn-delete')) {

            const id = target.dataset.id;

            if (!confirm('هل أنت متأكد؟')) return;

            const response = await fetch(`/comments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                target.closest('.comment-item').remove();
            }
        }
    });

        // بدء التعديل
        if (target.classList.contains('btn-edit')) {
            const id = target.dataset.id;
            const p = document.querySelector(`.comment-body-text[data-id="${id}"]`);
            const oldText = p.innerText;

            p.dataset.oldText = oldText; // حفظ النص القديم للطوارئ
            p.innerHTML = `
                <textarea class="form-control edit-input">${oldText}</textarea>
                <button class="btn-save-edit btn btn-sm btn-success mt-2" data-id="${id}">حفظ</button>
                <button type="button" class="btn-cancel-edit btn btn-sm btn-link mt-2">تراجع</button>
            `;
        }

        // تراجع عن التعديل
        if (target.classList.contains('btn-cancel-edit')) {
            const p = target.closest('.comment-body-text');
            p.innerText = p.dataset.oldText;
        }

        // حفظ التعديل النهائي
        if (target.classList.contains('btn-save-edit')) {
            const id = target.dataset.id;
            // const newBody = target.previousElementSibling.value;
            const parent = target.closest('.comment-body-text');
            const newBody = parent.querySelector('.edit-input').value;


            const response = await fetch(`/comments/${id}`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                 },
                body: JSON.stringify({ body: newBody })
            });

            if (response.ok) {
                const data = await response.json();
                parent.innerText = data.body; // تحديث بدون reload 🔥
            }
        }
    });

    // 3. مستمع أحداث لإرسال الرد (Submit Reply)
    document.addEventListener('submit', async (e) => {
        if (e.target.classList.contains('reply-form')) {
            e.preventDefault();
            const form = e.target;
            const id = form.dataset.id;
            const body = form.querySelector('textarea').value;
            const container = document.querySelector(`.replies-container[data-id="${id}"]`);

            const response = await fetch("{{ route('comments.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                 },
                body: JSON.stringify({ body: body, parent_id: id })
            });

            if (response.ok) {
                const data = await response.json();
                container.insertAdjacentHTML('beforeend', data.html);
                form.remove();
            }
        }
    });
});
</script>
@endpush --}}