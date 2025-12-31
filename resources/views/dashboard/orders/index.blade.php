@extends('layouts.dashboard')

@section('title', 'Orders')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Orders</li>
@endsection

@section('content')

<div class="mb-5">
     <a href="{{ route('dashboard.products.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- filter --}}
{{-- <form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4"> --}}
<form action="{{ request()->url() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="number" placeholder="order number" class="mx-2" :value="request('number')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
            @foreach (['pending' => 'pending' ,'processing' => 'processing', 'completed' => 'completed', 'cancelled' => 'cancelled', 'refunded' => 'refunded'] as $value => $label )
                <option value="{{ $value }}" @selected(request('status') == $value)>
                    {{ $label }}
                </option>
            @endforeach
    </select>
    <button class="btn btn-dark mx-2">Filter</button>
</form>

<table class="table">
    <thead>
        <tr>
            
            <th>ID</th>
            <th>Number</th>
            <th>Customer Name</th>
            <th>Total Price</th>
            <th>Payment Method</th>
            <th>Payment Status</th>
            <th>Status</th>
            <th>update status</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
            {{-- <th colspan="2"></th> --}}
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr>
            <td>{{ $order->id }}</td>
            <td> <a href="{{ route('dashboard.orders.show', $order->id) }}">{{ $order->number }}</td>
            <td>{{ $order->user?->name ?? 'Guest' }}</td>
            <td>{{ $order->total }}</td>
            <td>{{ $order->payment_method}}</td>
            <td>{{ $order->payment_status}}</td>
            <td>
                @php
                    $badgeClass = [
                        'pending' => 'bg-warning',
                        'processing' => 'bg-info',
                        'completed' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        'refunded' => 'bg-danger'
                    ][$order->status] ?? 'bg-secondary';
                @endphp
                <span class="badge {{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
            </td>
            <td>
                <form action="{{ route('dashboard.orders.update', $order->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    @method('PUT')
                    
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </form>
            </td>
            <td>{{ $order->created_at }}</td>
            <td>{{ $order->updated_at }}</td>
            <td>
                <form action="{{ route('dashboard.orders.destroy', $order->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No Orders Defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }} 
{{ $orders->links('pagination::bootstrap-5') }} 


{{ $orders
->withQueryString()->appends(['search' => 1])
->links() }}


@endsection