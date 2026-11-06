@extends('layouts.dashboard')

@section('title', __('app.notifications'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.notifications') }}</li>
@endsection

@section('content')
    @include('notifications._list')
@endsection
