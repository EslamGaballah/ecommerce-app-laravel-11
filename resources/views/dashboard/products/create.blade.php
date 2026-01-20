@extends('layouts.dashboard')

@section('title', __ ('app.products'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.products')}}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.products.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.products._form')
</form>

@endsection