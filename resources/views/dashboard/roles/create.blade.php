@extends('layouts.dashboard')

@section('title', __('app.create_role'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __('app.roles') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.roles.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.roles._form')
</form>

@endsection