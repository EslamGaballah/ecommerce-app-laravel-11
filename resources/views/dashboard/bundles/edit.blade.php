@extends('layouts.dashboard')

@section('title', $bundle->name)

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('dashboard.bundles.index') }}">{{ __('app.bundles') }}</a></li>
    <li class="breadcrumb-item active">{{ $bundle->name }}</li>
@endsection

@section('content')
<form action="{{ route('dashboard.bundles.update', $bundle) }}" method="POST">
    @csrf
    @method('PUT')
    @include('dashboard.bundles._form')
</form>
@endsection
