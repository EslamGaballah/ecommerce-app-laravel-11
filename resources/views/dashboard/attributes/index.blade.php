@extends('layouts.dashboard')

@section('title', __('app.attributes'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.attributes')}}</li>
@endsection

@section('content')

<div class="mb-5">
    @if(auth()->user()->can('create-categories'))
        <a href="{{ route('dashboard.attributes.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
        {{-- <a href="{{ route('dashboard.categories.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a>  --}}
     @endif
</div>

<x-alert type="success" />
<x-alert type="info" />


<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>{{__('app.name')}}</th>
            <th>{{__('app.values')}}</th>
            <th>{{__('app.created_at')}}</th>
            <th colspan="3">{{__('app.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attributes as $attribute)
        <tr>
            <td>{{ $attribute->id }}</td>
            <td><a href="{{ route('dashboard.attributes.show', $attribute->id) }}">{{ $attribute->name }}</a></td>

            <td>
                @if ($attribute->attributeValues)
                    @foreach ($attribute->attributeValues as $value)
                        <span class="btn btn-sm btn-outline-success"> {{ $value->value }} </span>
                    @endforeach
                @endif
            </td>
                
            <td>{{ $attribute->created_at }}</td>

            {{-- <td>
                @can('categories.update')
                <a href="{{ route('dashboard.attributes.show', $attribute->id) }}" class="btn btn-sm btn-outline-success">{{__('app.show')}}</a>
                @endcan
            </td>  --}}

            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.attributes.edit', $attribute->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                {{-- @endcan --}}
            </td> 
           
             <td> 
                {{-- @can('categories.delete') --}}
                <form action="{{ route('dashboard.attributes.destroy', $attribute->id) }}" method="post">
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
            <td colspan="9">No attributes defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $attributes->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}

@endsection