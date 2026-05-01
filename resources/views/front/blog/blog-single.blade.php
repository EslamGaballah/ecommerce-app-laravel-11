    <x-front-layout title="Checkout">

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
                                <li><a href="{{ route('home') }}">{{ __('app.blog') }}</a></li>
                                <li>{{$post->title}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:breadcrumb>

    <!-- Start Blog Singel Area -->
    <section class="section blog-single">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 col-md-12 col-12">
                    <div class="single-inner">
                        <div class="post-details">
                            <div class="main-content-head">
                                <div class="post-thumbnils"  style="width:100%; height:500px; overflow:hidden; object-fit:cover;">
                                    <img 
                                        src="{{ $post->images->first()?->image 
                                            ? asset('storage/' . $post->images->first()->image) 
                                            : 'لا يوجد صورة' }}"
                                    >                    
                                </div>
                                <div class="meta-information">
                                    <h2 class="post-title">
                                        <a href="">{{$post->title}}</a>
                                    </h2>
                                    <!-- End Meta Info -->
                                    <ul class="meta-info">
                                        <li>
                                            <a href="javascript:void(0)"> <i class="lni lni-user"></i> 
                                                {{$post->user->name ?? 'admin'}}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"><i class="lni lni-calendar"></i> {{$post->updated_at}}
                                            </a>
                                        </li>
                                        @if($post->category)
                                            <li>
                                                <a href="javascript:void(0)"><i class="lni lni-tag"></i> {{ $post->category->name }}</a>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="javascript:void(0)"><i class="lni lni-timer"></i> 5 min read</a>
                                        </li>
                                    </ul>
                                    <!-- End Meta Info -->
                                </div>
                                <div class="detail-inner">
                                    <p>{{$post->content}}</p>
                                   
                                    <div class="post-bottom-area">
                                        <!-- Start Post Tag -->
                                        @if($post->tags && $post->tags->count())                                        
                                            <div class="post-tag">
                                                <ul>
                                                    @foreach($post->tags as $tag)
                                                        <li>
                                                            <a href="{{ route('tags.show', $tag->slug) }}">
                                                                #{{$tag->name}}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <!-- End Post Tag -->
                                        <!-- Post Social Share -->
                                        <div class="post-social-media">
                                            <h5 class="share-title">{{ __('app.share_post') }} :</h5>
                                            <ul>
                                                <li>
                                                    <a href="javascript:void(0)">
                                                        <i class="lni lni-facebook-filled"></i>
                                                        <span>facebook</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)">
                                                        <i class="lni lni-twitter-original"></i>
                                                        <span>twitter</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)">
                                                        <i class="lni lni-google"></i>
                                                        <span>google+</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)">
                                                        <i class="lni lni-linkedin-original"></i>
                                                        <span>linkedin</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)">
                                                        <i class="lni lni-pinterest"></i>
                                                        <span>pinterest</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <!-- Post Social Share -->
                                    </div>
                                </div>
                            </div>
                            <!-- Comments -->
                            <div class="post-comments">
                                <h3 class="comment-title"><span>{{ __('app.comments') }}</span></h3>
                                <ul class="comments-list">
                                    @forelse($comments as $comment)
                                        @include('partials._comment', ['comment' => $comment])
                                    @empty
                                        <p>{{ __('app.no_comments_yet') }} .</p>
                                    @endforelse
                                </ul>
                            </div>
                            
                            <div class="comment-form">
                                <h3 class="comment-reply-title">{{ __('app.leave_comment') }}</h3>
                                    <form class="add-comment-form"
                                        action="{{ route('comments.store', $post->id) }}" 
                                        method="POST">
                                    @csrf
                                    <div class="row">
                         
                                        <div class="col-12">
                                            <div class="form-box form-group">
                                                <input type="hidden" name="post_id" value="{{ $post->id }}">
                                                <textarea name="body" class="form-control form-control-custom"
                                                    placeholder="{{ __('app.leave_comment') }}"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="button">
                                                <button type="submit" class="btn">{{ __('app.post_comment') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Blog Singel Area -->
</x-front-layout>

   

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>
@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.addEventListener('submit', function (e) {
        const form = e.target;

        // 1. التحقق: هل النموذج المرسل هو أحد نماذج التعليقات؟
        const isAdd = form.classList.contains('add-comment-form');
        const isUpdate = form.classList.contains('update-comment-form');
        const isDelete = form.classList.contains('delete-comment-form');

        if (isAdd || isUpdate || isDelete) {
            e.preventDefault(); // إيقاف الإرسال التقليدي فوراً

            // --- منطق الحذف (Confirm) ---
            if (isDelete) {
                if (!confirm('هل أنت متأكد من حذف هذا التعليق؟')) return;
            }

            // تجهيز البيانات
            // في حالة الحذف نرسل FormData المحتوية على _method DELETE
            const formData = new FormData(form);
            const action = form.action;
            const method = 'POST'; // نستخدم POST دائماً وLaravel سيفهم الـ _method المخفي

            fetch(action, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // --- تنفيذ الأكشن في الواجهة (UI) ---
                
                if (isDelete) {
                    // حذف العنصر من القائمة
                    form.closest('li').remove();
                } 
                
                else if (isUpdate) {
                    // تحديث نص التعليق وإخفاء الفورم
                    const bodyElement = document.getElementById('body-' + data.id);
                    if (bodyElement) bodyElement.innerText = data.body;
                    form.closest('.comment-form').classList.add('d-none');
                } 
                
                else if (isAdd) {
                    // إضافة تعليق جديد أو رد
                    if (form.querySelector('[name="parent_id"]')) {
                        // حالة الرد (Reply)
                        let parentLi = form.closest('li');
                        let repliesList = parentLi.querySelector('.comments-list');

                        if (!repliesList) {
                            repliesList = document.createElement('ul');
                            repliesList.classList.add('comments-list');
                            parentLi.appendChild(repliesList);
                        }
                        repliesList.insertAdjacentHTML('beforeend', data.html);
                        form.closest('.comment-form').classList.add('d-none'); // إخفاء فورم الرد
                    } else {
                        // حالة تعليق أساسي جديد
                        const mainList = document.querySelector('.post-comments .comments-list');
                        if (mainList) {
                            mainList.insertAdjacentHTML('afterbegin', data.html);
                        }
                    }
                    form.reset(); // تفريغ الحقول
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('عذراً، حدث خطأ ما. يرجى المحاولة مرة أخرى.');
            });
        }
    });
});
</script>
@endpush