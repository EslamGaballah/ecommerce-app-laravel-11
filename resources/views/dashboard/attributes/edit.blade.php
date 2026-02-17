@extends('layouts.dashboard')

@section('title', __ ('app.edit_attribute'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.attributes') }}</li>
<li class="breadcrumb-item active">{{ __ ('app.edit_attribute') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.attributes.update', $attribute->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.attributes._form', [
        'button_label' => 'Update'    
    ])
</form>

@endsection