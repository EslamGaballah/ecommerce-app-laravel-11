@extends('layouts.dashboard')

@section('title', __ ('app.edit_attribute_values'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.attribute_values') }}</li>
<li class="breadcrumb-item active">{{ __ ('app.edit_attribute_values') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.attribute_values.update', $attribute_value->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.attribute_values._form', [
        'button_label' => 'Update'    
    ])
</form>

@endsection