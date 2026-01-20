@extends('layouts.dashboard')

@section('title', __('app.categories'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.categories')}}</li>
@endsection

@section('content')

<div class="mb-5">
    @if(auth()->user()->can('create-categories'))
        <a href="{{ route('dashboard.categories.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
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
            <th>{{__('app.parent')}}</th>
            <th>{{__('app.description')}} </th>
            <th>{{__('app.products_count')}} </th>
            <th>{{__('app.status')}}</th>
            <th> {{__('app.created_at')}}</th>
            <th colspan="3">{{__('app.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($categories as $category)
        <tr>
            <td><img src="{{ asset('storage/' . $category->image) }}" alt="" height="50"></td>
            <td>{{ $category->id }}</td>
            <td><a href="{{ route('dashboard.categories.show', $category->id) }}">{{ $category->name }}</a></td>

            <td>
                @if ($category->parent)
                {{ $category->parent->name }}
                @endif
            </td>
            <td>{{ $category->description }}</td>

                
            <td>{{ $category->products_count }}</td>
            <td>{{ $category->status }}</td>
            <td>{{ $category->created_at }}</td>
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                {{-- @endcan --}}
            </td> 
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-success">{{__('app.update')}}</a>
                {{-- @endcan --}}
            </td> 
             <td> 
                {{-- @can('categories.delete') --}}
                <form action="{{ route('dashboard.categories.destroy', $category->id) }}" method="post">
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
            <td colspan="9">No categories defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $categories->appends(request()->query())->links('pagination::bootstrap-5') }}
{{-- {{ $categories->links('pagination::bootstrap-5') }} --}}

{{ $categories->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}

@endsection