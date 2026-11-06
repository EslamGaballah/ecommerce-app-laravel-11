@extends('layouts.dashboard')

@section('title', __('app.bundles'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.bundles') }}</li>
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('dashboard.bundles.create') }}" class="btn btn-sm btn-outline-primary">{{ __('app.create') }}</a>
</div>

<x-alert type="success" />

<table class="table">
    <thead>
        <tr>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.products') }}</th>
            <th>{{ __('app.bundle_discounts') }}</th>
            <th>{{ __('app.status') }}</th>
            <th class="text-center">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($bundles as $bundle)
            <tr>
                <td><a href="{{ route('dashboard.bundles.edit', $bundle) }}">{{ $bundle->name }}</a></td>
                <td>{{ $bundle->products_count }}</td>
                <td>
                    @foreach ($bundle->tiers as $tier)
                        <span class="badge bg-info">{{ __('app.bundle_tier_line', ['count' => $tier->min_quantity, 'discount' => $tier->label]) }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge bg-{{ $bundle->is_active ? 'success' : 'secondary' }}">{{ $bundle->is_active ? __('app.active') : __('app.inactive') }}</span>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <a href="{{ route('bundles.show', $bundle) }}" class="btn btn-sm btn-outline-info" target="_blank" rel="noopener">{{ __('app.view') }}</a>
                        <a href="{{ route('dashboard.bundles.edit', $bundle) }}" class="btn btn-sm btn-outline-success">{{ __('app.edit') }}</a>
                        <form action="{{ route('dashboard.bundles.destroy', $bundle) }}" method="POST" class="m-0" onsubmit="return confirm(@js(__('app.are_you_sure')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">{{ __('app.no_bundles') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $bundles->links('pagination::bootstrap-5') }}
@endsection
