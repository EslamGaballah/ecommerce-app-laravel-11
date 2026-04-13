@extends('layouts.dashboard')

@section('title', __('app.create_coupon'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.coupons') }}</li>
@endsection

@section('content')

    <form action="{{ route('dashboard.coupons.store') }}" method="POST">
        @csrf
        @include('dashboard.coupons._form', [
            'button_label' =>  __('app.create') 
            ])
    </form>

@endsection
