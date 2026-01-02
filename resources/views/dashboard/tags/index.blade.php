@extends('layouts.dashboard')

@section('title', 'Tags')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Tags</li>
@endsection

@section('content')

<div class="mb-5">
    @if(auth()->user()->can('create-tags'))
        <a href="{{ route('dashboard.Tags.create') }}" class="btn btn-sm btn-outline-primary mr-2">Create</a>
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
    <button class="btn btn-dark mx-2">Filter</button>
</form>
{{-- end filter --}}

<table class="table">
    <thead>
        <tr>
            
            <th>ID</th>
            <th>Name</th>
            <th>Product Count</th>
            <th>Created At</th>
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($tags as $tag)
        <tr>
            <td>{{ $tag->id }}</td>
            <td><a href="{{ route('dashboard.tags.show', $tag->id) }}">{{ $tag->name }}</a></td>

            

                
            <td>{{ $tag->products_number }}</td>
            <td>{{ $category->created_at }}</td>
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.tags.edit', $tag->id) }}" class="btn btn-sm btn-outline-success">Edit</a>
                {{-- @endcan --}}
            </td> 
             <td> 
                {{-- @can('categories.delete') --}}
                <form action="{{ route('dashboard.tags.destroy', $tag->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
                {{-- @endcan --}}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No tags defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $tag->appends(request()->query())->links('pagination::bootstrap-5') }}
{{ $tag->links('pagination::bootstrap-5') }}

{{ $tag->withQueryString()->appends(['search' => 1])->links() }}

@endsection