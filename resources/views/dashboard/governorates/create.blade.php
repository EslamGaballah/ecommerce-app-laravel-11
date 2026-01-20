@extends('layouts.dashboard')

@section('title', __ ('app.governorates'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active"> {{ __('app.governorates') }} </li>
@endsection

@section('content')


<form action="{{ route('dashboard.governorates.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.governorates._form')
    
</form>


@endsection