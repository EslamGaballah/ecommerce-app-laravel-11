@extends('layouts.dashboard')

@section('title', __ ('app.products'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.products')}}</li>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('dashboard.products.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.products._form')
</form>

@endsection