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
            <th colspan="2">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($favorites as $favorite)
        <tr>
            <td>
                @php
                    // نجيب الصورة حسب الحالة
                    $image = optional($favorite->default_variation)->image   // صورة الفارييشن الافتراضي لو موجود
                            ?? $favorite->image                              // صورة المنتج الأساسية
                            ?? $favorite->images->first()?->image           // أول صورة في gallery
                            ?? 'default.png';                               // صورة افتراضية لو مفيش أي صورة
                @endphp

                <img src="{{ asset('storage/' . $image) }}" alt="{{ $favorite->name }}" height="50">
            </td>
            <td>{{ $favorite->id }}</td>
            <td><a href="{{ route('dashboard.products.show', $favorite->id) }}">{{ $favorite->name }}</a></td>
            <td>
                {{ $favorite->category?->name ?? '-' }}
            </td>
            <td>{{ $favorite->description }}</td>

            <td>
                <form method="POST" action="{{ route('favorites.toggle', $favorite->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        {{ __('app.remove_from_favorites') }}
                    </button>
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
