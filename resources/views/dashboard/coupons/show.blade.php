@extends('layouts.dashboard')

@section('title', $coupons->name)

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{__('app.coupons')}}</li>
<li class="breadcrumb-item active">{{ $coupons->name }}</li>
@endsection

@section('content')

{{--<table class="table">--}}
{{--    <thead>--}}
{{--        <tr>--}}
{{--            <th>#</th>--}}
{{--            <th>{{__('app.name')}}</th>--}}
{{--            <th>{{__('app.stock')}}</th>--}}
{{--            <th>{{__('app.price')}}</th>--}}
{{--            <th>{{__('app.status')}}</th>--}}
{{--            <th> {{__('app.created_at')}}</th>--}}
{{--            <th colspan="2">{{__('app.actions')}}</th>--}}
{{--        </tr>--}}
{{--    </thead>--}}
{{--    <tbody>--}}
{{--        --}}
{{--        @forelse($coupons as $coupon)--}}
{{--        <tr>--}}
{{--            <td>{{ $coupon->name }}</td>--}}
{{--            <td>{{ $coupon->quantity }}</td>--}}
{{--            <td>{{ $product->price }}</td>--}}
{{--            <td>{{ $product->status }}</td>--}}
{{--            <td>{{ $product->created_at }}</td>--}}
{{--             <td>--}}
{{--                --}}{{-- @can('categories.update') --}}
{{--                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>--}}
{{--                --}}{{-- @endcan --}}
{{--            </td>--}}
{{--             <td>--}}
{{--                --}}{{-- @can('categories.delete') --}}
{{--                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post">--}}
{{--                    @csrf--}}
{{--                    <!-- Form Method Spoofing -->--}}
{{--                    <input type="hidden" name="_method" value="delete">--}}
{{--                    @method('delete')--}}
{{--                    <button type="submit" class="btn btn-sm btn-outline-danger">{{__('app.delete')}}</button>--}}
{{--                </form>--}}
{{--                --}}{{-- @endcan --}}
{{--            </td>--}}
{{--        </tr>--}}
{{--        @empty--}}
{{--        <tr>--}}
{{--            <td colspan="5">No products defined.</td>--}}
{{--        </tr>--}}
{{--        @endforelse--}}
{{--    </tbody>--}}
{{--</table>--}}

{{ $products->links() }}

@endsection
