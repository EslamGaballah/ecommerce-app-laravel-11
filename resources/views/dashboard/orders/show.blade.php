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
        <h2>{{ __('app.order_number') }}: {{ $order->number }}</h2>
        <div>
            <a href="{{ route('dashboard.orders.index') }}" class="btn btn-secondary btn-sm">العودة للقائمة</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">طباعة الفاتورة</button>
        </div>
    </div>

    <div class="row">
        {{-- تفاصيل العميل وسجل الحالات --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>{{ __('app.customer') }}</strong></div>
                <div class="card-body">
                    <p><strong>{{ __('app.name') }}:</strong> {{ $order->user?->name ?? 'ضيف' }}</p>
                    <p><strong>{{ __('app.email') }}:</strong> {{ $order->user?->email ?? 'N/A' }}</p>
                    <p><strong>{{ __('app.phone') }}:</strong> {{ $order->address->phone_number ?? 'N/A' }}</p>
                    <hr>
                    <h6>{{ __('app.shipping_address') }}</h6>
                    <p>
                        {{ $order->address->first_name }} {{ $order->address->last_name }}<br>
                        {{ $order->address->street_address }}<br>
                        {{ $order->address->city }}, {{ $order->address->country }}
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light"><strong>{{ __('app.status_history') }}</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.updated_by') }}</th>
                                    <th>{{ __('app.from') }}</th>
                                    <th>{{ __('app.to') }}</th>
                                    <th>{{ __('app.date') }}</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                @foreach($order->statusHistories as $history)
                                    <tr>
                                        <td>{{ $history->user?->name ?? '—' }}</td>

                                        <td>{{ ucfirst($history->old_status) }}</td>
                                        <td>{{ ucfirst($history->new_status) }}</td>
                                        <td>{{ $history->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- تفاصيل المنتجات وتحديث الحالة --}}
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>{{ __('app.products') }}</strong></div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('app.products') }}</th>
                                <th>{{ __('app.price') }}</th>
                                <th>{{ __('app.quantity') }}</th>
                                <th>{{ __('app.total') }}</th>
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

                        @php
                            $subtotal = $order->items->sum(function($item) {
                                return $item->price * $item->quantity;
                            });
                        @endphp
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">{{ __('app.total_price') }} :</th>
                                <td>{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">{{ __('app.discount') }} :</th>
                                <td class="text-danger">- {{ number_format($order->discount, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">{{ __('app.after_discount') }} :</th>
                                <td>{{ number_format($subtotal - $order->discount, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end text-success">{{ __('app.shipping') }} :</th>
                                <td>{{ number_format($order->shipping, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">{{ __('app.tax') }} :</th>
                                <td>{{ number_format($order->tax, 2) }}</td>
                            </tr>
                            <tr class="table-dark">
                                <th colspan="3" class="text-end">{{ __('app.total') }} :</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- تحديث الحالة --}}
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>{{ __('app.update_status') }}</strong></div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="badge bg-{{ $order->status->color() }} badge-status-{{ $order->id }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <select class="form-select order-status" 
                                    name="status" 
                                    data-id="{{ $order->id }}" 
                                    data-old="{{ $order->status->value }}">
                                @foreach(\App\Enums\OrderStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($order->status == $status)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    document.addEventListener('change', function (event) {
    if (!event.target.classList.contains('order-status')) return;

    const select = event.target;
    const newStatus = select.value;
    const oldStatus = select.getAttribute('data-old');
    const orderId = select.getAttribute('data-id');

    if (newStatus === oldStatus) return;

    select.disabled = true;

    fetch(`/dashboard/orders/${orderId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // تحديث القيمة القديمة
        select.setAttribute('data-old', data.status);
        
        // تحديث البادج (شغال في الصفحتين)
        const badgeEl = document.querySelector('.badge-status-' + orderId);
        if (badgeEl) {
            badgeEl.className = badgeEl.className.replace(/\bbg-\S+/g, '');
            badgeEl.classList.add('bg-' + data.color);
            badgeEl.textContent = data.label;
        }

        // تحديث اسم المعدل والوقت (خاص بصفحة الـ index)
        const updatedByEl = document.querySelector('.updated-by-' + orderId);
        const updatedAtEl = document.querySelector('.updated-at-' + orderId);
        if (updatedByEl) updatedByEl.textContent = data.updated_by;
        if (updatedAtEl) updatedAtEl.textContent = data.updated_at;

        // تحديث جدول الهيستوري (خاص بصفحة الـ show)
        const historyTable = document.getElementById('history-table-body');
        if (historyTable) {
            const newRow = document.createElement('tr');
            
            // حماية الدالة من القيم الفارغة لمنع الانهيار
            const safeOld = oldStatus ? capitalizeFirstLetter(oldStatus) : '—';
            const safeNew = newStatus ? capitalizeFirstLetter(newStatus) : '—';
            
            newRow.innerHTML = `
                <td>${data.updated_by}</td>
                <td>${safeOld}</td>
                <td>${safeNew}</td>
                <td>${data.updated_at}</td>
            `;
            historyTable.insertBefore(newRow, historyTable.firstChild);
        }
    })
    .catch(error => {
        alert('Something went wrong!');
        select.value = oldStatus; 
        console.error('Error:', error);
    })
    .finally(() => {
        select.disabled = false;
    });
});

function capitalizeFirstLetter(string) {
    if (!string) return '—'; // حماية إضافية لو النص فارغ
    return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>
@endpush