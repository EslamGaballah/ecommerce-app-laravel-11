@extends('layouts.dashboard')

@section('title', __('app.edit_role'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __('app.roles') }}</li>
<li class="breadcrumb-item active">{{ __('app.edit_role') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.roles.update', $role->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.roles._form', [
        'button_label' => __('app.update')    
    ])
</form>

@endsection