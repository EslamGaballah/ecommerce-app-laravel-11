@extends('layouts.dashboard')

@section('title', __ ('app.create_post') )

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.create_post')}}</li>
@endsection

@section('content')


<form action="{{ route('dashboard.posts.store') }}"
     method="post" 
     enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.posts._form',[
        'post'=> null,
        'button_label' => __('app.create')
    ])
    
</form>


@endsection