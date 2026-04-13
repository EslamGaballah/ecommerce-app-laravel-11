@extends('layouts.dashboard')

@section('title', 'Edit Product')

@section('breadcrumb')
@parent
<li class="breadcrumb-item">Products</li>
<li class="breadcrumb-item active">Edit Product</li>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('dashboard.products.update', $product->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.products._form', [
        'button_label' => __('app.update')    
    ])
</form>

@endsection