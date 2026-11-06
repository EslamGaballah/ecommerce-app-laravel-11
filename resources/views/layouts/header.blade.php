<header class="header navbar-area">
    @if (Setting::get('announcement'))
        <div class="announcement-bar">{{ Setting::get('announcement') }}</div>
    @endif


    {{-- Top bar --}}
    <div class="topbar">
        <div class="container">
            <div class="topbar-inner">
                <div class="topbar-side">
                    @foreach (config('app.available_locales') as $localeCode => $details)
                        @continue($localeCode === app()->getLocale())
                        <a href="{{ route('lang.switch', $localeCode) }}" class="topbar-link" lang="{{ $localeCode }}">
                            <i class="lni lni-world"></i> {{ $details['name'] }}
                        </a>
                    @endforeach

                    @if (Setting::get('phone'))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', Setting::get('phone')) }}" class="topbar-link topbar-phone" dir="ltr">
                            <i class="lni lni-phone"></i> {{ Setting::get('phone') }}
                        </a>
                    @endif
                </div>

                <nav class="topbar-pages" aria-label="{{ __('app.information') }}">
                    <a href="{{ route('pages.about') }}">{{ __('app.about') }}</a>
                    <a href="{{ route('front.posts.index') }}">{{ __('app.blog') }}</a>
                    <a href="{{ route('pages.faq') }}">{{ __('pages.faq.title') }}</a>
                    <a href="{{ route('contact') }}">{{ __('app.contact') }}</a>
                </nav>

                <div class="topbar-side topbar-end">
                    @if (Setting::get('dark_mode_enabled'))
                        <button type="button" class="topbar-link topbar-theme" data-theme-toggle aria-label="{{ __('app.toggle_theme') }}">
                            <i class="lni lni-night theme-icon-dark"></i>
                            <i class="lni lni-sun theme-icon-light"></i>
                        </button>
                    @endif

                    @auth
                        <div class="dropdown">
                            <a class="topbar-link dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="topbar-avatar">
                                @else
                                    <i class="lni lni-user"></i>
                                @endif
                                <span>{{ auth()->user()->name }}</span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                @can('access-dashboard')
                                    <li><a class="dropdown-item" href="{{ route('dashboard.index') }}">{{ __('app.dashboard') }}</a></li>
                                @endcan
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('app.profile') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('front.orders.index') }}">{{ __('app.my_orders') }}</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">{{ __('app.logout') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="topbar-link"><i class="lni lni-user"></i> {{ __('app.login') }}</a>
                                                @if (Setting::get('registration_enabled'))
                            <a href="{{ route('register') }}" class="topbar-link">{{ __('app.register') }}</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Logo, search, and icons --}}
    <div class="header-middle">
        <div class="container">
            <div class="header-middle-inner">
                <a class="navbar-brand" href="{{ route('home') }}">
                    @if (Setting::get('logo'))
                        <img src="{{ asset('storage/'.Setting::get('logo')) }}" alt="{{ Setting::get('site_name') }}">
                    @else
                        <img src="{{ asset('assets/images/logo/logo.svg') }}" alt="{{ Setting::get('site_name') }}" class="logo-light">
                        <img src="{{ asset('assets/images/logo/white-logo.svg') }}" alt="{{ Setting::get('site_name') }}" class="logo-dark">
                    @endif
                </a>

                <div class="main-menu-search">
                    <form action="{{ route('products.index') }}" method="GET" class="navbar-search search-style-5">
                        <div class="search-select">
                            <div class="select-position">
                                <select name="category" id="select1" aria-label="{{ __('app.categories') }}">
                                    <option value="">{{ __('app.all') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(request()->query('category') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="search-input">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search') }}" aria-label="{{ __('app.search') }}">
                        </div>
                        <div class="search-btn">
                            <button aria-label="{{ __('app.search') }}"><i class="lni lni-search-alt"></i></button>
                        </div>
                    </form>
                </div>

                <div class="middle-right-area">
                    @if (Setting::get('phone'))
                        <div class="nav-hotline">
                            <i class="lni lni-phone"></i>
                            <h3>{{ __('app.hotline') }}:
                                <span dir="ltr">{{ Setting::get('phone') }}</span>
                            </h3>
                        </div>
                    @endif

                    <div class="navbar-cart">
                        @auth
                            @php($unreadNotifications = auth()->user()->unreadNotifications)
                            <div class="cart-items">
                                <a href="javascript:void(0)" class="main-btn" aria-label="{{ __('app.notifications') }}">
                                    <i class="lni lni-alarm"></i>
                                    <span class="total-items" id="notification-count">{{ $unreadNotifications->count() }}</span>
                                </a>
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>{{ __('app.unread_notifications', ['count' => $unreadNotifications->count()]) }}</span>
                                        <a href="{{ route('notifications.index') }}">{{ __('app.notifications') }}</a>
                                    </div>
                                    <ul class="shopping-list" id="notification-list">
                                        @forelse ($unreadNotifications->take(5) as $notification)
                                            <li>
                                                <a href="{{ route('notifications.open', $notification->id) }}">
                                                    @include('partials.notification-text')
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-muted px-3">{{ __('app.no_notifications') }}</li>
                                        @endforelse
                                    </ul>
                                    <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">{{ __('app.see_all_notifications') }}</a>
                                </div>
                            </div>
                        @endauth

                        <div class="wishlist">
                            <a href="{{ route('favorites.index') }}" aria-label="{{ __('app.favorites') }}">
                                <i class="lni lni-heart"></i>
                                <span class="total-items favorites-count">{{ auth()->check() ? auth()->user()->favorites()->count() : 0 }}</span>
                            </a>
                        </div>

                        <x-cart-menu />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories and main menu --}}
    <div class="header-bottom">
        <div class="container">
            <div class="header-bottom-inner">
                <div class="nav-inner">
                    @include('partials.category-menu')
                    @include('partials.nav')
                </div>

                @if (Setting::get('show_social_header') && App\Models\Setting::hasSocialLinks())
                    <div class="nav-social">
                        <h5 class="title">{{ __('app.follow_us') }}:</h5>
                        <ul>
                            @include('partials.social-links')
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
