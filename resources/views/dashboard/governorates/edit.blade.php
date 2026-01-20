@extends('layouts.dashboard')

@section('title', __ ('app.edit_governorates'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.governorates') }}</li>
<li class="breadcrumb-item active">{{ __ ('app.governorates') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.governorates.update', $governorate->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.governorates._form', [
        'button_label' => 'Update'    
    ])
</form>

@endsection