<header class="header navbar-area">
        <!-- Start Topbar -->
    <div class="topbar">
        <div class="container" style="display: inline">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="top-left">
                        <ul class="menu-top-link">
                            <li>
                                <div class="select-position">
                                    <form 
                                    action="{{ route('home') }}"
                                        method="post">
                                        @csrf
                                        <select name="currency_code" onchange="this.form.submit()">
                                            <option value="USD" @selected('USD' == session('currency_code'))>$ USD</option>
                                            <option value="EUR" @selected('EUR' == session('currency_code'))>€ EURO</option>
                                        </select>
                                    </form>
                                </div>
                            </li>

                            <li>
                                <div class="select-position">
                                    <select name="lang" onchange="window.location.href='{{ route('lang.switch', '') }}/' + this.value">
                                        @foreach(config('app.available_locales') as $localeCode => $details)
                                            <option value="{{ $localeCode }}" 
                                                @selected($localeCode == app()->getLocale())>
                                                {{ $details['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="top-middle">
                        <ul class="useful-links">
                            <li><a href="{{ route('home') }}">{{ trans('app.home') }}</a></li>
                            <li><a href="about-us.html">@lang('app.about')</a></li>
                            <li><a href="contact.html">{{ __('app.contact') }}</a></li>
                        </ul>
                    </div>
                </div>
                {{-- start user --}}
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="top-end">
                        @auth
                            <div class="user">
                                <i class="lni lni-user"></i>
                                {{-- {{ Auth::user()->name }} --}}
                                {{ \Illuminate\Support\Facades\Auth::user()->name }}
                            </div>
                            <ul class="user-login">
                                <li>
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout').submit()">{{ __('app.logout') }}</a>
                                </li>
                                <form action="{{ route('logout') }}" id="logout" method="post" style="display:none">
                                    @csrf
                                </form>
                            </ul>
                        @else
                            <div class="user">
                                <i class="lni lni-user"></i>
                                {{ __('Hello')}}
                            </div>
                            <ul class="user-login">
                                <li>
                                    {{-- <a href="{{ route('login') }}">{{ Lang::get('Sign In') }}</a> --}}
                                    <a href="{{ route('login') }}">{{ __('app.login') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('register') }}">{{ __('app.register') }}</a>
                                </li>
                            </ul>
                        @endauth

                    </div>
                {{-- end user --}}
                </div>
            </div>
        </div>
        <!-- End Topbar -->
    </div>
        
        <!-- Start Header Middle -->
    <div class="header-middle">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-3 col-7">
                    <!-- Start Header Logo -->
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/logo/logo.svg') }}" alt="Logo">
                    </a>
                    <!-- End Header Logo -->
                </div>
                <div class="col-lg-5 col-md-7 d-xs-none">

                    <!-- Start Main Menu Search -->
                    <div class="main-menu-search">
                        <!-- navbar search start -->
                        <div class="navbar-search search-style-5">
                            <div class="search-select">
                                <div class="select-position">
                                    <select id="select1">
                                        <option selected>{{__('app.all')}}</option>
                                        <option value="1">option 01</option>
                                        <option value="2">option 02</option>
                                        <option value="3">option 03</option>
                                    </select>
                                </div>
                            </div>
                            <div class="search-input">
                                <input type="text" placeholder="Search (header.blade header middle)">
                            </div>
                            <div class="search-btn">
                                <button><i class="lni lni-search-alt"></i></button>
                            </div>
                            <!-- navbar search Ends -->
                        </div>
                        <!-- End Main Menu Search -->
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-2 col-5">
                    <div class="middle-right-area">
                        <!-- Start Hotline area -->
                        <div class="nav-hotline">
                            <i class="lni lni-phone"></i>
                            <h3>{{ __('app.hotline') }}:
                                <span>(+20) 1091070473</span>
                            </h3>
                        </div>
                         <!-- Notifications Start -->
                        <div class="navbar-cart">
                            <div class="cart-items">
                                <a href="javascript:void(0)" class="main-btn">
                                    <i class="lni lni-heart"></i>
                                     <span class="total-items" 
                                        id="notification-count" 
                                        >
                                        @auth
                                            {{ auth()->user()->unreadNotifications->count() }}
                                        @endauth
                                    </span>
                                </a>
                                @auth
                                    <div class="shopping-item">
                                        <div class="dropdown-cart-header">
                                            <span id="notification-count" >
                                                (  {{ auth()->user()->unreadNotifications->count() }} )
                                                un read Notifications
                                            </span>
                                            <a href="{{ route('notifications.index') }}">{{ __('app.notifications') }}</a>
                                        </div>
                                         <ul class="shopping-list"
                                            id="notification-list"
                                             >
                                            @forelse(auth()->user()->unreadNotifications as $notification)
                                                <li>
                                                    <a href="{{ route('dashboard.orders.show', $notification->data['order_id'] ?? '#') }}">
                                                        {{ $notification->data['message'] ?? $notification->data['body'] ?? 'إشعار جديد' }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li class="text-muted px-3">لا يوجد إشعارات</li>
                                            @endforelse
                                        </ul>
                                    {{-- <div class="dropdown-divider"></div> --}}
                                    <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">
                                        See All Notifications
                                    </a>
                                    </div>
                                @endauth
                                <!-- Notifications End -->
                            </div>
                            <div class="wishlist">
                                {{-- <a href="javascript:void(0)"> --}}
                                <a href=" {{ route('favorites.index') }}">
                                    <i class="lni lni-heart"></i>
                                    <span class="total-items">
                                        {{ auth()->check() ? auth()->user()->favorites()->count() : 0 }}
                                    </span>
                                </a>
                            </div>
                            {{-- start cart menu --}}
                            <x-cart-menu />
                            {{-- end cart menu --}}

                            <div class="cart-items">
                                <a href="javascript:void(0)" class="main-btn">
                                    <i class="lni lni-user"></i>
                                </a>
                                <!-- Shopping Items -->
                                <ul class="shopping-list">
                                <div class="shopping-item">
                                    {{-- <div class="dropdown-cart-header"> --}}
                                        <li class="nav-item">
                                            <a href="{{ route('home') }}">
                                                my profile
                                            </a>
                                        </li>
                                    {{-- </div> --}}

                                        {{-- <div class="dropdown-cart-header"> --}}
                                        <li class="nav-item">
                                            <a href="{{ route('home') }}">
                                                {{ __('app.orders') }}
                                            </a>
                                        </li>
                                    {{-- </div> --}}
                                    
                                        @if (!auth()->check())
                                            <li class="nav-item active"><a href="{{route('login')}}">{{ __('app.login') }}</a></li>
                                            <li class="nav-item"><a href="{{route('register')}}">{{ __('app.register') }}</a></li>
                                        @endif

                                        @if (auth()->check())
                                            <li class="nav-item active">
                                                <form method="POST" action="{{ route('logout') }}" class="nav-link">
                                                    @csrf
                                                    <button type="submit">{{ __('app.logout') }}</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>

                                </div>
                                <!--/ End Shopping Items -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <!-- End Header Middle -->

        <!-- Start Header Bottom -->
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-6 col-12">
                <div class="nav-inner">
                    <!-- Start Mega Category Menu -->
                        @include('partials.category-menu')
                    <!-- End Mega Category Menu -->

                    <!-- Start Navbar -->
                        @include('partials.nav')
                    <!-- End Navbar -->

                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <!-- Start Nav Social -->
                <div class="nav-social">
                    <h5 class="title">{{ __('app.follow_us') }}:</h5>
                    <ul>
                        <li>
                            <a href="javascript:void(0)"><i class="lni lni-facebook-filled"></i></a>
                        </li>
                        <li>
                            <a href="javascript:void(0)"><i class="lni lni-twitter-original"></i></a>
                        </li>
                        <li>
                            <a href="javascript:void(0)"><i class="lni lni-instagram"></i></a>
                        </li>
                        <li>
                            <a href="https://wa.me/201091070473" target="_blank">
                                <i class="lni lni-whatsapp"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- End Nav Social -->
            </div>
        </div>
    </div>
        <!-- End Header Bottom -->
</header>