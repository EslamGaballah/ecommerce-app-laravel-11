@extends('layouts.dashboard')

@section('title', 'Orders')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.orders')}}</li>
@endsection

@section('content')

<div class="mb-5">
     <a href="{{ route('dashboard.products.trash') }}" class="btn btn-sm btn-outline-dark">{{__('app.trash')}}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- filter --}}
{{-- <form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4"> --}}
<form action="{{ request()->url() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="number" placeholder="order number" class="mx-2" :value="request('number')" />
    <select name="status" class="form-control mx-2">
    <option value="">{{ __('All') }}</option>

        @foreach(\App\Enums\OrderStatus::cases() as $status)
            <option 
                value="{{ $status->value }}"
                @selected(request('status') == $status->value)
            >
                {{ $status->label() }}
            </option>
        @endforeach
        
    </select>

    <button class="btn btn-dark mx-2">{{__('app.filter')}}</button>
</form>

<table class="table">
    <thead>
        <tr>
            
            <th>ID</th>
            <th> {{__('app.order_number')}} </th>

            <th>{{__('app.total_price')}}</th>
            <th>{{__('app.payment_method')}}</th>
            <th>{{__('app.payment_status')}}</th>
            <th>{{__('app.status')}}</th>
            {{-- <th>update status</th> --}}
            <th>{{__('app.updated_by')}}</th>
            <th>{{__('app.updated_at')}}</th>
             <th>{{__('app.created_at')}}</th>
            <th colspan="2"> {{__('app.actions')}}</th>
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
            <td><span class="badge bg-{{ $order->payment_method->color() }}">
                    {{ $order->payment_method>label() }}
                </span>
            </td>
            <td><span class="badge bg-{{ $order->payment_status->color() }}">
                    {{ $order->payment_status>label() }}
                </span>
            </td>
            <td>
                
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



                {{-- <form action="{{ route('dashboard.orders.update', $order->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    @method('PUT')
                    
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </form> --}}
            </td>
            <td class="updated-by-{{ $order->id }}">
                {{ $order->updatedBy?->name ?? '—' }}
            </td>
            <td class="updated-at-{{ $order->id }}">
                {{ $order->updated_at }}
            </td>
            <td>{{ $order->created_at }}</td>
            
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