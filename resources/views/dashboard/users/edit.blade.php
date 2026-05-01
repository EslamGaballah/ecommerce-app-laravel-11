@extends('layouts.dashboard')

@section('title', __('app.edit') )

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{__('app.users')}}</li>
<li class="breadcrumb-item active">{{__('app.edit')}}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.users.update', $user->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.users._form', [
        'button_label' => __('app.update')  
    ])
</form>

@endsection