@extends('layouts.dashboard')

@section('title', 'Edit Tag')

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">Tags</li>
<li class="breadcrumb-item active">Edit Tag</li>
@endsection

@section('content')

<form action="{{ route('dashboard.tags.update', $category->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.tags._form', [
        'button_label' => 'Update'    
    ])
</form>

@endsection