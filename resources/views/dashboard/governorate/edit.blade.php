@extends('layouts.dashboard')

@section('title', __ ('app.edit_category'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __ ('app.categories') }}</li>
<li class="breadcrumb-item active">{{ __ ('app.edit_category') }}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.categories.update', $category->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.categories._form', [
        'button_label' => 'Update'    
    ])
</form>

@endsection