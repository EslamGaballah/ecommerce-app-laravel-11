@extends('layouts.dashboard')

@section('title', __('app.edit'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item ">
        <a href="{{ route('dashboard.brands.index') }}">
            {{ __('app.brands') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('app.edit_brand') }}
    </li>
@endsection

@section('content')

<form action="{{ route('dashboard.brands.update', $brand) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.brands._form', [
        'button_label' =>  __('app.update')    
    ])
</form>

@endsection