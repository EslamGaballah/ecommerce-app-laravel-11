@extends('layouts.dashboard')

@section('title', __('app.coupons'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.coupons')}}</li>
@endsection

@section('content')

    <div class="mb-5">
        @if(auth()->user()->can('create-coupons'))
            <a href="{{ route('dashboard.coupons.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
        @endif
    </div>

    <x-alert type="success" />
    <x-alert type="info" />

    {{-- start filter --}}
    <form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4">
        <x-form.input name="code" placeholder="Code" class="mx-2" :value="request('code')" />
        <select name="status" class="form-control mx-2">
            <option value="">All</option>
            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-dark mx-2">{{__('app.filter')}}</button>
    </form>
    {{-- end filter --}}

    <table class="table">
        <thead>
       <tr>
            <th>#</th>
            <th>{{ __('app.coupon_code') }}</th>
            <th>{{ __('app.type') }}</th>
            <th>{{ __('app.value') }}</th>
            <th>{{ __('app.usage_limit') }}</th>
            <th>{{ __('app.used_count') }}</th>
            <th>{{ __('app.expiry_date') }}</th>
            <th>{{ __('app.status') }}</th>
            <th colspan="3">{{ __('app.actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($coupons as $coupon)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $coupon->code }}</td>
                <td>{{ ucfirst($coupon->type) }}</td>
                <td>
                    @if($coupon->type == 'percent')
                        {{ $coupon->value }}%
                    @else
                        {{ $coupon->value }} {{ __('app.currency') }}
                    @endif
                </td>
                <td>{{ $coupon->usage_limit ?? '∞' }}</td>
                <td>{{ $coupon->used_count }}</td>
                <td>{{ $coupon->expires_at?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ $coupon->active ? 'Active' : 'Inactive' }}</td>
                <td>
                    @can('update-coupons')
                        <a href="{{ route('dashboard.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                    @endcan
                </td>
                
                <td>
                    @can('delete-coupons')
                        <form action="{{ route('dashboard.coupons.destroy', $coupon->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')">{{__('app.delete')}}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12">{{ __('app.no_coupons_defined') }}.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $coupons->appends(request()->query())->links('pagination::bootstrap-5') }}

@endsection
