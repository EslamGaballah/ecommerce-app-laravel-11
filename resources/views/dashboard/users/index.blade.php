@extends('layouts.dashboard')

@section('title', 'users')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.users')}}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.users.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />


{{-- start filter --}}
<form id="filter-form" action="{{ url()->current() }}" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" id="search-name" placeholder="Name" class="mx-2" :value="request('name')" />
    
    <select name="user_type" id="user-type-select" class="form-control mx-2">
        <option value="">All</option>
        <option value="admins" @selected(request('user_type') == 'admins')>{{ __('الإدارة') }}</option>
        <option value="regular" @selected(request('user_type') == 'regular')>{{ __('مستخدم عادي') }}</option>
    </select>
    
    <button type="submit" class="btn btn-dark mx-2">{{__('app.filter')}}</button>
</form>
{{-- end filter --}}


<div id="users-table">
    @include('dashboard.users._table') 
</div>


@endsection

@push('script')
<script>
$(document).ready(function() {
    // 1. دالة جلب البيانات
    function fetchUsers(url = "{{ url()->current() }}") {
        let formData = $('#filter-form').serialize(); 
        
        $.ajax({
            url: url,
            data: formData,
            beforeSend: function() {
                $('#users-table').css('opacity', '0.5'); // تعتيم بسيط أثناء التحميل
            },
            success: function(response) {
                $('#users-table').html(response);
                $('#users-table').css('opacity', '1');
            }
        });
    }

    // 2. تحديث تلقائي عند تغيير الـ Select (بدون الحاجة لزر)
    $('#user-type-select').on('change', function() {
        fetchUsers();
    });

    // 3. البحث عند الكتابة (مع Delay بسيط)
    let timer;
    $('#search-name').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(fetchUsers, 500);
    });

    // 4. معالجة الـ Pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        fetchUsers($(this).attr('href'));
    });

    // 5. منع التحديث عند الضغط على Enter
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        fetchUsers();
    });
});
</script>
@endpush