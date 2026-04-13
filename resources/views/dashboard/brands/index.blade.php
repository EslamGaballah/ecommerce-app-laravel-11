@extends('layouts.dashboard')

@section('title', __('app.brands'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.brands')}}</li>
@endsection

@section('content')

<div class="mb-5">
    @if(auth()->user()->can('create-categories'))
        <a href="{{ route('dashboard.brands.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
        {{-- <a href="{{ route('dashboard.categories.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a>  --}}
     @endif
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- start filter --}}
<form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
       
            @foreach (['active' => 'Active' ,'arvived' => 'Archived'] as $value => $label )
                <option value="{{ $value }}" @selected(request('status') == $value)>
                    {{ $label }}
                </option>
            @endforeach
    </select>
    <button class="btn btn-dark mx-2">{{__('app.filter')}}</button>
</form>
{{-- end filter --}}

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>{{__('app.name')}}</th>
            {{-- <th>{{__('app.parent')}}</th> --}}
            <th>{{__('app.description')}} </th>
            <th>{{__('app.products_count')}} </th>
            <th>{{__('app.status')}}</th>
            <th> {{__('app.created_at')}}</th>
            <th colspan="3">{{__('app.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($brands as $brand)
        <tr>
            <td><img src="{{ asset('storage/' . $brand->image) }}" alt="" height="50"></td>
            <td>{{ $brand->id }}</td>
            <td><a href="{{ route('dashboard.brands.show', $brand->id) }}">{{ $brand->name }}</a></td>

            <td>{{ $brand->description }}</td>

                
            <td>{{ $brand->products_count }}</td>
            <td>{{ $brand->status }}</td>
            <td>{{ $brand->created_at }}</td>
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.brands.edit', $brand->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                {{-- @endcan --}}
            </td> 
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.brands.edit', $brand->id) }}" class="btn btn-sm btn-outline-success">{{__('app.update')}}</a>
                {{-- @endcan --}}
            </td> 
             <td> 
                {{-- @can('categories.delete') --}}
                <form action="{{ route('dashboard.brands.destroy', $brand->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{__('app.delete')}}</button>
                </form>
                {{-- @endcan --}}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No brands defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- {{ $brands->appends(request()->query())->links('pagination::bootstrap-5') }} --}}
{{-- {{ $categories->links('pagination::bootstrap-5') }} --}}

{{ $brands->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}

@endsection