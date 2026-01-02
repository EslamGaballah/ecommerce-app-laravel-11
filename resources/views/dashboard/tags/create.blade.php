@extends('layouts.dashboard')

@section('title', 'Tags')

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">Tags</li>
@endsection

@section('content')


<form action="{{ route('dashboard.tags.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    @include('dashboard.tags._form')
    
</form>


@endsection