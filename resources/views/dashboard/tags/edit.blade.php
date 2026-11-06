@extends('layouts.dashboard')

@section('title', __('app.edit_tag'))

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{__('app.tags')}}</li>
<li class="breadcrumb-item active">{{__('app.edit')}}</li>
@endsection

@section('content')

<form action="{{ route('dashboard.tags.update', $tag->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    @include('dashboard.tags._form', [
        'button_label' => __('app.update')    
    ])
</form>

@endsection