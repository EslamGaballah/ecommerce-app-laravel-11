@extends('layouts.dashboard')

@section('title', 'Trashed Categories')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item">{{ __('app.tags') }}</li>
    <li class="breadcrumb-item active">{{ __('app.trash') }}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.categories.index') }}" class="btn btn-sm btn-outline-primary">Back</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

<form action="{{ URL::current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
        <option value="active" @selected(request('status') == 'active')>{{ __('app.active') }}</option>
        <option value="archived" @selected(request('status') == 'archived')>{{ __('app.archived') }}</option>
    </select>
    <button class="btn btn-dark mx-2">{{ __('app.filter') }}</button>
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
        @forelse($categories as $category)
        <tr>
            <td><img src="{{ asset('storage/' . $category->image) }}" alt="" height="50"></td>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->status }}</td>
            <td>{{ $category->deleted_at }}</td>
            <td>
                <form action="{{ route('dashboard.categories.restore', $category->id) }}" method="post">
                    @csrf
                    @method('put')
                    <button type="submit" class="btn btn-sm btn-outline-info">{{ __('app.restore') }}</button>
                </form>
            </td>
            <td>
                <form action="{{ route('dashboard.categories.force-delete', $category->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.force_delete') }}</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7">No tags defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $categories->withQueryString()->appends(['search' => 1])->links() }}

@endsection