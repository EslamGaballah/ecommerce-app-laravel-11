@extends('layouts.dashboard')

@section('title', $tag->name)

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __('app.tags') }}</li>
<li class="breadcrumb-item active">{{ $tag->name }}</li>
@endsection

@section('content')

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('app.name') }}</th>
            
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.created_at') }}</th>
        </tr>
    </thead>
    <tbody>
        
        @forelse($tag->products as $product)
        <tr>
            <td><img src="{{ asset('storage/' . $product->image) }}" alt="" height="50"></td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->status }}</td>
            <td>{{ $product->created_at }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5">No products defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>


@endsection