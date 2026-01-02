@extends('layouts.dashboard')

@section('title', 'Favorites')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Favorites</li>
@endsection

@section('content')

<x-alert type="success" />
<x-alert type="info" />

<table class="table">
    <thead>
        <tr>
            <th>image</th>
            <th>ID</th>
            <th>Product</th>
            <th>Category</th>
            <th>description </th>
            <th>stock</th>
            <th colspan="2"></th>
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
                    {{ $favorite->products_number }}
                @else
                    <span class="text-danger">out of stock</span>
                @endif
            </td>
            <td> 
                <form method="POST" action="{{ route('favorites.destroy', $favorite->id) }}">
                    @csrf
                        <input type="hidden" name="_method" value="delete">
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove from Favorites</button>
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