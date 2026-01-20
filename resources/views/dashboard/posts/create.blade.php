@extends('layouts.dashboard')

@section('title', 'Create Post')

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">Create post</li>
@endsection

@section('content')


<form action="{{ route('dashboard.posts.store') }}"
     method="post" 
     enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.posts._form',[
        'post'=> null,
        'button_label' => 'Create'
    ])
    
</form>


@endsection