@extends('layouts.dashboard')

@section('title', 'Edit Admin')

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{__('app.users')}}</li>
<li class="breadcrumb-item active">{{__('app.edit')}}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.admins.update', $admin->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.admins._form', [
        'button_label' => 'Update'    
    ])
</form>

@endsection