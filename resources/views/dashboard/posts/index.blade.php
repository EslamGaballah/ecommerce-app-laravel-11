@extends('layouts.dashboard')

@section('title', 'Blog')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Blog</li>
@endsection

@section('content')

<div class="mb-5">
    {{-- @if(auth()->user()->can('create-posts')) --}}
        <a href="{{ route('dashboard.posts.create') }}" class="btn btn-sm btn-outline-primary mr-2">Create</a>
        {{-- <a href="{{ route('dashboard.categories.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a>  --}}
     {{-- @endif --}}
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- start filter --}}
<form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="title" placeholder="title" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
       
            @foreach (['published' => 'published' ,'arvived' => 'Archived'] as $value => $label )
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
            <th>#</th>
            <th>ID</th>
            <th>title</th>
            <th>Author </th>
            <th>Status</th>
            <th>Created At</th>
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($posts as $post)
        <tr>
            <td>
                @if ($post->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $post->images->first()->image) }}" 
                        alt="" height="50">
                @else
                    <span>No Image</span>
                @endif
            </td>

            <td>{{ $post->id }}</td>
            <td><a href="{{ route('dashboard.posts.show', $post->id) }}">{{ $post->title }}</a></td>
            <td>{{ $post->user?->name }}</td>
            {{-- <td>{{ $category->products_number }}</td> --}}
            <td>{{ $post->status }}</td>
            <td>{{ $post->created_at }}</td>
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-success">Edit</a>
                {{-- @endcan --}}
            </td> 
             <td> 
                {{-- @can('categories.delete') --}}
                <form action="{{ route('dashboard.posts.destroy', $post->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    {{-- <input type="hidden" name="_method" value="delete"> --}}
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
                {{-- @endcan --}}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No Posts defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $posts->withQueryString()->appends(['search' => 1])->links() }}

@endsection