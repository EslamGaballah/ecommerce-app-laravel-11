@extends('layouts.dashboard')

@section('title', __('app.bundle_create'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('dashboard.bundles.index') }}">{{ __('app.bundles') }}</a></li>
    <li class="breadcrumb-item active">{{ __('app.create') }}</li>
@endsection

@section('content')
<form action="{{ route('dashboard.bundles.store') }}" method="POST">
    @csrf
    @include('dashboard.bundles._form')
</form>
@endsection
