@php($unreadNotifications = auth()->user()->unreadNotifications)

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" aria-label="Menu"><i class="lni lni-menu"></i></a>
        </li>
        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener">
                <i class="lni lni-home"></i>
                <span class="dash-hide-sm">{{ __('app.view_store') }}</span>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav dash-nav-end">
        <li class="nav-item">
            @if (app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="nav-link" lang="en"><i class="lni lni-world"></i><span class="dash-hide-sm">English</span></a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="nav-link" lang="ar"><i class="lni lni-world"></i><span class="dash-hide-sm">العربية</span></a>
            @endif
        </li>

                @if (Setting::get('dark_mode_enabled'))
            <li class="nav-item">
                <a class="nav-link" href="#" data-theme-toggle aria-label="{{ __('app.toggle_theme') }}" onclick="event.preventDefault()">
                    <i class="lni lni-night theme-icon-dark"></i>
                    <i class="lni lni-sun theme-icon-light"></i>
                </a>
            </li>
        @endif

        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-label="{{ __('app.notifications') }}">
                <i class="far fa-bell"></i>
                <span @class(['badge', 'navbar-badge', 'd-none' => $unreadNotifications->isEmpty()]) id="notification-count">{{ $unreadNotifications->count() }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-header">{{ __('app.unread_notifications', ['count' => $unreadNotifications->count()]) }}</span>
                <div class="dropdown-divider"></div>
                <ul class="list-unstyled mb-0" id="notification-list">
                    @forelse ($unreadNotifications->take(5) as $notification)
                        <li>
                            @if (isset($notification->data['order_id']))
                                <a href="{{ route('notifications.open', $notification->id) }}" class="dropdown-item">@include('partials.notification-text')</a>
                            @else
                                <div class="dropdown-item">@include('partials.notification-text')</div>
                            @endif
                        </li>
                    @empty
                        <li><span class="dropdown-item text-muted text-center">{{ __('app.no_notifications') }}</span></li>
                    @endforelse
                </ul>
                <div class="dropdown-divider"></div>
                <a href="{{ route('dashboard.notifications.index') }}" class="dropdown-item dropdown-footer text-center">{{ __('app.see_all_notifications') }}</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-label="{{ auth()->user()->name }}">
                <span class="dash-avatar">@if (auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="">@else{{ mb_substr(auth()->user()->name, 0, 1) }}@endif</span>
                <span class="dash-hide-sm">{{ auth()->user()->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('dashboard.profile.edit') }}" class="dropdown-item dash-dropdown-link"><i class="lni lni-user"></i>{{ __('app.profile') }}</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="dropdown-item dash-dropdown-button text-danger"><i class="lni lni-exit"></i>{{ __('app.logout') }}</button>
                </form>
            </div>
        </li>
    </ul>
</nav>
