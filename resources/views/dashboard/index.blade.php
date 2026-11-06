@extends('layouts.dashboard')

@section('title', __('app.dashboard'))

@section('content')
<div class="dash-welcome">
    <h2>{{ __('app.welcome_back', ['name' => auth()->user()->name]) }}</h2>
    <p>{{ __('app.dashboard_intro') }}</p>
</div>

<div class="dash-stats">
    @if ($orderStats)
        <a href="{{ route('dashboard.orders.index') }}" class="dash-stat">
            <span class="dash-stat-icon"><i class="lni lni-dollar"></i></span>
            <span>
                <span class="dash-stat-label">{{ __('app.total_revenue') }}</span>
                <span class="dash-stat-value">{{ Currency::format($orderStats['revenue']) }}</span>
                <span class="dash-stat-note">{{ __('app.this_month_amount', ['amount' => Currency::format($orderStats['monthRevenue'])]) }}</span>
            </span>
        </a>
        <a href="{{ route('dashboard.orders.index') }}" class="dash-stat">
            <span class="dash-stat-icon"><i class="lni lni-cart"></i></span>
            <span>
                <span class="dash-stat-label">{{ __('app.total_orders') }}</span>
                <span class="dash-stat-value">{{ $orderStats['total'] }}</span>
                <span class="dash-stat-note">{{ __('app.today_count', ['count' => $orderStats['today']]) }}</span>
            </span>
        </a>
    @endif

    @if ($productStats)
        <a href="{{ route('dashboard.products.index') }}" class="dash-stat">
            <span class="dash-stat-icon"><i class="lni lni-package"></i></span>
            <span>
                <span class="dash-stat-label">{{ __('app.total_products') }}</span>
                <span class="dash-stat-value">{{ $productStats['total'] }}</span>
                <span class="dash-stat-note">{{ __('app.active_count', ['count' => $productStats['active']]) }}</span>
            </span>
        </a>
        <a href="{{ route('dashboard.products.index') }}" class="dash-stat">
            <span class="dash-stat-icon is-danger"><i class="lni lni-warning"></i></span>
            <span>
                <span class="dash-stat-label">{{ __('app.out_of_stock') }}</span>
                <span class="dash-stat-value">{{ $productStats['outOfStock'] }}</span>
                <span class="dash-stat-note">{{ __('app.active_products_only') }}</span>
            </span>
        </a>
    @endif

    @if ($userStats)
        <a href="{{ route('dashboard.users.index') }}" class="dash-stat">
            <span class="dash-stat-icon"><i class="lni lni-users"></i></span>
            <span>
                <span class="dash-stat-label">{{ __('app.total_users') }}</span>
                <span class="dash-stat-value">{{ $userStats['total'] }}</span>
                <span class="dash-stat-note">{{ __('app.new_this_week', ['count' => $userStats['newThisWeek']]) }}</span>
            </span>
        </a>
    @endif
</div>

<div class="row">
    @if ($orderStats)
        <div class="{{ $productStats ? 'col-lg-8' : '' }} col-12 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header dash-panel-header">
                    <h3>{{ __('app.recent_orders') }}</h3>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('app.view_all') }}</a>
                </div>
                @if ($orderStats['recent']->isEmpty())
                    <p class="dash-empty">{{ __('app.no_orders_yet') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.order_number') }}</th>
                                    <th>{{ __('app.customer') }}</th>
                                    <th>{{ __('app.total') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderStats['recent'] as $order)
                                    <tr>
                                        <td><a href="{{ route('dashboard.orders.show', $order) }}">{{ $order->number }}</a></td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ Currency::format($order->total) }}</td>
                                        <td><span class="badge bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                                        <td class="text-nowrap">{{ $order->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($productStats)
        <div class="{{ $orderStats ? 'col-lg-4' : '' }} col-12 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header dash-panel-header">
                    <h3>{{ __('app.low_stock') }}</h3>
                </div>
                @if ($productStats['lowStock']->isEmpty())
                    <p class="dash-empty">{{ __('app.no_low_stock') }}</p>
                @else
                    <ul class="dash-stock-list">
                        @foreach ($productStats['lowStock'] as $product)
                            <li>
                                <img src="{{ $product->image_url }}" alt="">
                                <a href="{{ route('dashboard.products.show', $product) }}">{{ $product->name }}</a>
                                <span class="badge bg-{{ $product->stock_left > 0 ? 'warning' : 'danger' }}">{{ $product->stock_left }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
</div>

@if ($productReports)
    <div class="row">
        @foreach ([
            ['title' => __('app.best_sellers'), 'items' => $productReports['bestSellers'], 'empty' => __('app.report_no_sales'), 'badge' => fn ($product) => (int) $product->sold_quantity, 'color' => 'success'],
            ['title' => __('app.highest_stock'), 'items' => $productReports['highestStock'], 'empty' => __('app.report_empty'), 'badge' => fn ($product) => $product->stock_left, 'color' => 'info'],
            ['title' => __('app.stagnant_products', ['days' => $productReports['stagnantDays']]), 'items' => $productReports['stagnant'], 'empty' => __('app.report_no_stagnant'), 'badge' => fn ($product) => $product->stock_left, 'color' => 'secondary'],
        ] as $report)
            <div class="col-lg-4 col-12 mb-3">
                <div class="card h-100 mb-0">
                    <div class="card-header dash-panel-header">
                        <h3>{{ $report['title'] }}</h3>
                    </div>
                    @if ($report['items']->isEmpty())
                        <p class="dash-empty">{{ $report['empty'] }}</p>
                    @else
                        <ul class="dash-stock-list">
                            @foreach ($report['items'] as $product)
                                <li>
                                    <img src="{{ $product->image_url }}" alt="">
                                    <a href="{{ route('dashboard.products.show', $product) }}">{{ $product->name }}</a>
                                    <span class="badge bg-{{ $report['color'] }}">{{ $report['badge']($product) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
