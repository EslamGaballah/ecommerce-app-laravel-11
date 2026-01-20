@extends('layouts.dashboard')

@section('title', 'Edit Post')

@section('breadcrumb')
@parent
{{-- <li class="breadcrumb-item active">Categories</li> --}}
<li class="breadcrumb-item active">Edit Post</li>
@endsection

@section('content')

<form action="{{ route('dashboard.post.update', $post->id) }}"
     method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.posts._form', [
        'post' => $post,
        'button_label' => 'Update'    
    ])
</form>

@endsection