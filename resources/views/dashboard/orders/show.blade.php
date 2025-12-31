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
        <h2>تفاصيل الطلب #{{ $order->number }}</h2>
        <div>
            <a href="{{ route('dashboard.orders.index') }}" class="btn btn-secondary btn-sm">العودة للقائمة</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">طباعة الفاتورة</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>معلومات العميل</strong></div>
                <div class="card-body">
                    <p><strong>الاسم:</strong> {{ $order->user?->name ?? 'ضيف' }}</p>
                    <p><strong>البريد:</strong> {{ $order->user?->email ?? 'N/A' }}</p>
                    <hr>
                    <h6>عنوان الشحن</h6>
                    <p>
                        {{ $order->address->first_name }} {{ $order->address->last_name }}<br>
                        {{ $order->address->street_address }}<br>
                        {{ $order->address->city }}, {{ $order->address->country }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>المنتجات المطلوبة</strong></div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>المنتج</th>
                                <th>السعر</th>
                                <th>الكمية</th>
                                <th>الإجمالي</th>
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
                                <th colspan="3" class="text-end">المجموع الفرعي:</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">الخصم :</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">الاجمالى بعد الخصم :</th>
                                <td>{{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end text-success">الشحن:</th>
                                <td>0.00</td> 
                            </tr>
                            <tr class="table-dark">
                                <th colspan="3" class="text-end">الإجمالي الكلي:</th>
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
                                <label>تحديث حالة الطلب الحالية:</label>
                                <select name="status" class="form-select">
                                    <option value="pending" @selected($order->status == 'pending')>قيد الانتظار</option>
                                    <option value="processing" @selected($order->status == 'processing')>قيد التنفيذ</option>
                                    <option value="completed" @selected($order->status == 'completed')>مكتمل</option>
                                    <option value="cancelled" @selected($order->status == 'cancelled')>ملغي</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-4">
                                <button type="submit" class="btn btn-success">تحديث الحالة</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
