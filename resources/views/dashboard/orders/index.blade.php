@extends('layouts.dashboard')

@section('title', 'Orders')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.orders') }}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.products.trash') }}" class="btn btn-sm btn-outline-dark">{{ __('app.trash') }}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- Filter --}}
<form action="{{ request()->url() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="number" placeholder="Order number" class="mx-2" :value="request('number')" />
    
    <select name="status" class="form-control mx-2">
        <option value="">{{ __('All') }}</option>
        @foreach(\App\Enums\OrderStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                {{ $status->label() }}
            </option>
        @endforeach
    </select>

    <button class="btn btn-dark mx-2">{{ __('app.filter') }}</button>
</form>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('app.order_number') }}</th>
                <th>{{ __('app.customer') }}</th>
                <th>{{ __('app.total_price') }}</th>
                <th>{{ __('app.payment_method') }}</th>
                <th>{{ __('app.payment_status') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('app.update_status') }}</th>
                <th>{{ __('app.updated_by') }}</th>
                <th>{{ __('app.updated_at') }}</th>
                <th>{{ __('app.created_at') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td><a href="{{ route('dashboard.orders.show', $order->id) }}">{{ $order->number }}</a></td>
                {{-- <td>{{ $order->user?->name ?? 'Guest' }}</td> --}}
                <td>
                    {{  ($order->address?->first_name . ' ' . $order->address?->last_name) 
                        ?? $order->user?->name 
                        ?? 'Guest' 
                    }}
                </td>
                <td>{{ $order->total }}</td>
                <td>
                    <span class="badge bg-{{ $order->payment_method->color() }}">
                        {{ $order->payment_method->label() }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-{{ $order->payment_status->color() }}">
                        {{ $order->payment_status->label() }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-{{ $order->status->color() }} badge-status-{{ $order->id }}">
                        {{ $order->status->label() }}
                    </span>
                </td>
                <td>
                    <select class="form-select form-select-sm order-status" 
                            name="status" 
                            data-id="{{ $order->id }}" 
                            data-old="{{ $order->status->value }}">
                        @foreach(\App\Enums\OrderStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($order->status == $status)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="updated-by-{{ $order->id }}">
                   
                    {{ $order->updatedBy?->user ? $order->updatedBy->user->name : '—' }}
                </td>
                <td class="updated-at-{{ $order->id }}">
                    {{ $order->updated_at }}
                </td>
                <td>{{ $order->created_at }}</td>
                <td>
                    <form action="{{ route('dashboard.orders.destroy', $order->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">No Orders Defined.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4">
    {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
</div>

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

@endsection