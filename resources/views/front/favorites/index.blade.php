@extends('layouts.dashboard')

@section('title', 'Favorites')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.favorites') }}</li>
@endsection

@section('content')

<x-alert type="success" />
<x-alert type="info" />

<table class="table">
    <thead>
        <tr>
            <th>{{ __('app.images') }}</th>
            <th>ID</th>
            <th>{{ __('app.products') }}</th>
            <th>{{ __('app.categories') }}</th>
            <th>{{ __('app.description') }} </th>
            <th>{{ __('app.stock') }}</th>
            <th colspan="2">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($favorites as $favorite)
        <tr>
            <td><img src="{{ asset('storage/' . $favorite->image) }}" alt="" height="50"></td>
            <td>{{ $favorite->id }}</td>
            <td><a href="{{ route('dashboard.products.show', $favorite->id) }}">{{ $favorite->name }}</a></td>
            <td>
                {{ $favorite->category?->name ?? '-' }}
            </td>
            <td>{{ $favorite->description }}</td>
            <td>
                @if ($favorite->products_number > 0)
                   {{ __('app.in_stock') }}
                @else
                    <span class="text-danger">{{ __('app.out_of_stock') }}</span>
                @endif
            </td>
            <td> 
                <form method="POST" action="{{ route('favorites.toggle', $favorite->id) }}">
                    @csrf
                        <input type="hidden" name="_method" value="delete">
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{__('app.remove_from_favorites')}}</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No favorites defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $favorites->withQueryString()->links('pagination::bootstrap-5') }}

@endsection