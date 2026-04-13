@extends('layouts.dashboard')

@section('title', __('app.edit_coupon'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item ">
        <a href="{{ route('dashboard.coupons.index') }}">
            {{ __('app.coupons') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('app.edit_coupons') }}
    </li>
@endsection

@section('content')

    <form action="{{ route('dashboard.coupons.update', $coupon) }}" method="POST">
        @csrf
        @method('PUT')
        @include('dashboard.coupons._form', [
            'button_label' =>  __('app.update') ])
    </form>

@endsection
