@extends('layouts.dashboard')

@section('title', __ ('app.edit'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item ">
        <a href="{{ route('dashboard.categories.index') }}">
            {{ __('app.categories') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('app.edit_category') }}
    </li>
@endsection

@section('content')

<form action="{{ route('dashboard.categories.update', $category) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.categories._form', [
        'button_label' =>  __('app.update')    
    ])
</form>

@endsection