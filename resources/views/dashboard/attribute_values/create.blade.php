@extends('layouts.dashboard')

@section('title', __ ('app.attribute_values'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active"> {{ __('app.attribute_values') }} </li>
@endsection

@section('content')


<form action="{{ route('dashboard.attribute_values.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.attribute_values._form')
    
</form>


@endsection