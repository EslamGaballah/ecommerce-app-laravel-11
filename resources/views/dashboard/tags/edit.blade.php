@extends('layouts.dashboard')

@section('title', 'Edit Tag')

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{__('app.tags')}}</li>
<li class="breadcrumb-item active">{{__('app.edit')}}</li>
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