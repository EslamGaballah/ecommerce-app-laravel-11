@extends('layouts.dashboard')

@section('title', __ ('app.attributes'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active"> {{ __('app.attributes') }} </li>
@endsection

@section('content')


<form action="{{ route('dashboard.attributes.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.attributes._form')
    
</form>


@endsection