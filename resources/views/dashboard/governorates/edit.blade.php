@extends('layouts.dashboard')

@section('title', __ ('app.governorates'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item ">
    <a href="{{ route('dashboard.governorates.index') }}">
        {{ __('app.governorates') }}
    </a>
</li>
<li class="breadcrumb-item active">{{ __ ('app.governorates') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.governorates.update', $governorate->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')

    @include('dashboard.governorates._form', [
        'button_label' => __('app.update')
    ])
</form>

@endsection
