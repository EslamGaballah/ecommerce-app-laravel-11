@extends('layouts.dashboard')

@section('title', __('app.profile'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.profile') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-12">
        @include('profile.partials.update-profile-information-form', ['updateRoute' => 'dashboard.profile.update'])
        @include('profile.partials.update-password-form')
    </div>
</div>
@endsection
