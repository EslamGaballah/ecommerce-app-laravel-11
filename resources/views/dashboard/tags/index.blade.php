@extends('layouts.dashboard')

@section('title', 'Tags')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.tags')}}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.tags.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- start filter --}}
<form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <button class="btn btn-dark mx-2">{{__('app.filter')}}</button>
</form>
{{-- end filter --}}

<table class="table">
    <thead>
        <tr>
            
            <th>ID</th>
            <th>{{__('app.name')}}</th>
            <th>{{__('app.numper')}}</th>
            <th>{{__('app.created_at')}}</th>
            <th colspan="2">{{__('app.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tags as $tag)
        <tr>
            <td>{{ $tag->id }}</td>
            <td><a href="{{ route('dashboard.tags.show', $tag->id) }}">{{ $tag->name }}</a></td>

            

                
            <td>{{ $tag->products_count }}</td>
            <td>{{ $tag->created_at }}</td>
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.tags.edit', $tag->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                {{-- @endcan --}}
            </td> 
             <td> 
                {{-- @can('categories.delete') --}}
                <form action="{{ route('dashboard.tags.destroy', $tag->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.delete') }}</button>
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

{{ $tags->withQueryString()->links('pagination::bootstrap-5') }}

@endsection