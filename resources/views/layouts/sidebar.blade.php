@php
    $sections = [
        null => [
            ['route' => 'dashboard.index', 'active' => 'dashboard.index', 'icon' => 'lni-dashboard', 'label' => 'dashboard'],
        ],
        'menu_catalog' => [
            ['route' => 'dashboard.products.index', 'active' => 'dashboard.products.*', 'icon' => 'lni-package', 'label' => 'products', 'can' => 'view-product'],
            ['route' => 'dashboard.categories.index', 'active' => 'dashboard.categories.*', 'icon' => 'lni-grid-alt', 'label' => 'categories', 'can' => 'view-categories'],
            ['route' => 'dashboard.brands.index', 'active' => 'dashboard.brands.*', 'icon' => 'lni-bookmark', 'label' => 'brands', 'can' => 'manage-brands'],
            ['route' => 'dashboard.tags.index', 'active' => 'dashboard.tags.*', 'icon' => 'lni-tag', 'label' => 'tags', 'can' => 'manage-tags'],
            ['route' => 'dashboard.attributes.index', 'active' => 'dashboard.attributes.*', 'icon' => 'lni-layers', 'label' => 'attributes', 'can' => 'manage-attributes'],
            ['route' => 'dashboard.attribute-values.index', 'active' => 'dashboard.attribute-values.*', 'icon' => 'lni-list', 'label' => 'attribute_values', 'can' => 'manage-attributes'],
        ],
        'menu_sales' => [
                        ['route' => 'dashboard.bundles.index', 'active' => 'dashboard.bundles.*', 'icon' => 'lni-gift', 'label' => 'bundles', 'can' => 'manage-bundles'],
            ['route' => 'dashboard.orders.index', 'active' => 'dashboard.orders.*', 'icon' => 'lni-cart', 'label' => 'orders', 'can' => 'view-order'],
            ['route' => 'dashboard.coupons.index', 'active' => 'dashboard.coupons.*', 'icon' => 'lni-ticket', 'label' => 'coupons', 'can' => 'manage-coupons'],
            ['route' => 'dashboard.governorates.index', 'active' => 'dashboard.governorates.*', 'icon' => 'lni-map-marker', 'label' => 'governorates', 'can' => 'manage-governorates'],
        ],
        'menu_content' => [
            ['route' => 'dashboard.posts.index', 'active' => 'dashboard.posts.*', 'icon' => 'lni-pencil-alt', 'label' => 'blog', 'can' => 'manage-posts'],
        ],
        'menu_administration' => [
            ['route' => 'dashboard.messages.index', 'active' => 'dashboard.messages.*', 'icon' => 'lni-envelope', 'label' => 'contact_messages', 'can' => 'manage-messages', 'badge' => auth()->user()->can('manage-messages') ? App\Models\ContactMessage::whereNull('read_at')->count() : 0],
            ['route' => 'dashboard.users.index', 'active' => 'dashboard.users.*', 'icon' => 'lni-users', 'label' => 'users', 'can' => 'manage-users'],
            ['route' => 'dashboard.roles.index', 'active' => 'dashboard.roles.*', 'icon' => 'lni-shield', 'label' => 'roles', 'can' => 'manage-roles'],
            ['route' => 'dashboard.settings.edit', 'active' => 'dashboard.settings.*', 'icon' => 'lni-cog', 'label' => 'settings', 'can' => 'manage-settings'],
        ],
    ];
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-0">
    <a href="{{ route('dashboard.index') }}" class="brand-link">
        @if (Setting::get('logo'))
            <img src="{{ asset('storage/'.Setting::get('logo')) }}" alt="" class="dash-brand-logo">
        @else
            <span class="dash-brand-mark"><i class="lni lni-cart"></i></span>
        @endif
        <span class="brand-text">{{ Setting::get('site_name') }}</span>
    </a>

    <div class="sidebar">
        <div class="user-panel">
            <span class="dash-avatar">@if (auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="">@else{{ mb_substr(auth()->user()->name, 0, 1) }}@endif</span>
            <div class="info">
                <a href="{{ route('dashboard.profile.edit') }}" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @foreach ($sections as $title => $items)
                    @php($visible = array_filter($items, fn ($item) => ! isset($item['can']) || auth()->user()->can($item['can'])))
                    @if ($visible)
                        @if ($title)
                            <li class="nav-header">{{ __('app.'.$title) }}</li>
                        @endif
                        @foreach ($visible as $item)
                            <li class="nav-item">
                                <a href="{{ route($item['route']) }}" @class(['nav-link', 'active' => request()->routeIs($item['active'])])>
                                    <i class="nav-icon lni {{ $item['icon'] }}"></i>
                                    <p>
                                        {{ __('app.'.$item['label']) }}
                                        @if (! empty($item['badge']))
                                            <span class="right badge bg-primary">{{ $item['badge'] }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
