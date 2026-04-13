@extends('layouts.dashboard')

@section('title', __('app.brands'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.brands') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.brands.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.brands._form')
    
</form>

@endsection