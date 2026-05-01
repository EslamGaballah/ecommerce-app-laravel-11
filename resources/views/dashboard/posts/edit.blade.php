@extends('layouts.dashboard')

@section('title', __('app.edit_post'))

@section('breadcrumb')
@parent
{{-- <li class="breadcrumb-item active">Categories</li> --}}
<li class="breadcrumb-item active"> {{ __('app.edit_post') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.posts.update', $post->id) }}"
     method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.posts._form', [
        'post' => $post,
        'button_label' => __('app.update') 
    ])
</form>

@endsection