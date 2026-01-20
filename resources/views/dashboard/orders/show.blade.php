@extends('layouts.dashboard')

@section('title', $order->name)

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">Orders</li>
<li class="breadcrumb-item active">{{ $order->name }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{__('app.order_number')}}:{{ $order->number }}</h2>
        <div>
            <a href="{{ route('dashboard.orders.index') }}" class="btn btn-secondary btn-sm">العودة للقائمة</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">طباعة الفاتورة</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>{{__('app.customer')}}</strong></div>
                <div class="card-body">
                    <p><strong>{{__('app.name')}}:</strong> {{ $order->user?->name ?? 'ضيف' }}</p>
                    <p><strong>{{__('app.email')}}:</strong> {{ $order->user?->email ?? 'N/A' }}</p>
                    <hr>
                    <h6> {{__('app.shipping_address')}}</h6>
                    <p>
                        {{ $order->address->first_name }} {{ $order->address->last_name }}<br>
                        {{ $order->address->street_address }}<br>
                        {{ $order->address->city }}, {{ $order->address->country }}
                    </p>
                </div>

                 <h5>{{__('app.states_history')}}</h5>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>{{__('app.updated_by')}}</th>
                        <th>{{__('app.date')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->statusHistories as $history)
                        <tr>
                            <td>{{ ucfirst($history->old_status) }}</td>
                            <td>{{ ucfirst($history->new_status) }}</td>
                            <td>{{ $history->user?->name ?? '—' }}</td>
                            <td>{{ $history->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>{{__('app.products')}} </strong></div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>{{__('app.products')}}</th>
                                <th>{{__('app.price')}}</th>
                                <th>{{__('app.quantity')}}</th>
                                <th>{{__('app.total')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">{{__('app.products_total_price')}} :</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">{{__('app.discount')}} :</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">{{__('app.total')}}   :</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end text-success">{{__('app.shipping')}}:</th>
                                <td>0.00</td> 
                            </tr>
                            <tr class="table-dark">
                                <th colspan="3" class="text-end"> {{__('app.total')}}:</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('dashboard.orders.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <td>
                {{-- @php
                    $badgeClass = [
                        'pending' => 'bg-warning',
                        'processing' => 'bg-info',
                        'completed' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        'refunded' => 'bg-danger'
                    ][$order->status] ?? 'bg-secondary';
                @endphp --}}
                <span class="badge bg-{{ $order->status->color() }} badge-{{ $order->id }}">
                    {{ $order->status->label() }}
                </span>
            </td>
            <td>
                <select 
                    class="form-select form-select-sm order-status"
                    name="status"
                    data-id="{{ $order->id }}"
                    data-old="{{ $order->status->value }}"
                >
                    @foreach(\App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($order->status == $status)>
                           {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

<script>

    $(document).on('change', '.order-status', function () {

        let select   = $(this);
        let newStatus = select.val();
        let oldStatus = select.data('old');
        let orderId   = select.data('id');

        // same status
        if (newStatus === oldStatus) {
            return;
        }

        select.prop('disabled', true);

        $.ajax({
            url: `/dashboard/orders/${orderId}`,
            type: 'PUT',
            data: {
                status: newStatus,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {

                // update old value
                select.data('old', response.status);

                // updated by
                $('.updated-by-' + orderId).text(response.updated_by);

                // updated at
                $('.updated-at-' + orderId).text(response.updated_at);

                // badge
                // let badgeClass = {
                //     pending: 'bg-warning',
                //     processing: 'bg-info',
                //     completed: 'bg-success',
                //     cancelled: 'bg-danger',
                //     refunded: 'bg-danger'
                // };

                let badge = $('.badge-' + orderId);
                badge
                    .removeClass(function (index, className) {
                    return (className.match(/bg-\S+/g) || []).join(' ');
                })
                   .addClass('bg-' + response.color)
                    .text(response.label);
            },
            complete: function () {
                select.prop('disabled', false);
            }
        });
    });

</script>

@endpush


@endsection
