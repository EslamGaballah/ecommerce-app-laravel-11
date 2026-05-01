@extends('layouts.dashboard')

@section('title', __ ('app.trash'))


@section('breadcrumb')
    @parent
    <li class="breadcrumb-item">{{ __('app.products') }}</li>
    <li class="breadcrumb-item active">{{ __('app.trash') }}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.products.index') }}" class="btn btn-sm btn-outline-primary">{{ __('app.back') }}</a>
</div>

{{-- <x-alert type="success" />
<x-alert type="info" /> --}}

<form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
        <option value="active" @selected(request('status') == 'active')>Active</option>
        <option value="archived" @selected(request('status') == 'archived')>Archived</option>
    </select>
    <button class="btn btn-dark mx-2">Filter</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th></th>
            <th>ID</th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.deleted_at') }}</th>
            <th colspan="2">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td><img src="{{ asset('storage/' . $product->image) }}" alt="" height="50"></td>
            <td>{{ $product->id }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->status }}</td>
            <td>{{ $product->deleted_at }}</td>
            <td>
                <form action="{{ route('dashboard.products.restore', $product->id) }}" method="post">
                    @csrf
                    @method('put')
                    <button type="submit" class="btn btn-sm btn-outline-info">{{ __('app.restore') }}</button>
                </form>
            </td>
            <td>
                <form action="{{ route('dashboard.products.forceDelete', $product->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.delete') }}</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7">{{ __('app.no_products_defined') }}.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $products->withQueryString()->appends(['search' => 1])->links() }}

@endsection