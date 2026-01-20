 <nav class="navbar navbar-expand-lg">
                            <button class="navbar-toggler mobile-menu-btn" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ms-auto">

                                    @can('access-dashboard')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.index') }}">{{__('app.dashboard')}}</a>
                                        </li>
                                    @endcan

                                    <li class="nav-item">
                                        <a href="{{ route('home') }}" aria-label="Toggle navigation">{{__('app.home')}}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="dd-menu active collapsed" href="javascript:void(0)"
                                            data-bs-toggle="collapse" data-bs-target="#submenu-1-2"
                                            aria-controls="navbarSupportedContent" aria-expanded="false"
                                            aria-label="Toggle navigation">{{ __('app.pages') }}</a>
                                        <ul class="sub-menu collapse" id="submenu-1-2">
                                            <li class="nav-item"><a href="about-us.html">{{ __('app.about_us') }}</a></li>
                                            <li class="nav-item"><a href="faq.html">Faq</a></li>

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

                                            <li class="nav-item"><a href="mail-success.html">Mail Success</a></li>
                                            <li class="nav-item"><a href="404.html">404 Error</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="dd-menu collapsed" href="javascript:void(0)" data-bs-toggle="collapse"
                                            data-bs-target="#submenu-1-3" aria-controls="navbarSupportedContent"
                                            aria-expanded="false" aria-label="Toggle navigation">{{ __('app.shop') }}</a>
                                        <ul class="sub-menu collapse" id="submenu-1-3">
                                            <li class="nav-item"><a href="{{ route('products.index') }}">{{ __('app.products') }}</a></li>
                                            <li class="nav-item"><a href="{{ route('cart.index') }}">{{ __('app.cart') }}</a></li>
                                            <li class="nav-item"><a href="{{ route('checkout') }}">{{ __('app.checkout') }}</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="dd-menu collapsed" href="javascript:void(0)" data-bs-toggle="collapse"
                                            data-bs-target="#submenu-1-4" aria-controls="navbarSupportedContent"
                                            aria-expanded="false" aria-label="Toggle navigation">{{ __('app.blog') }}</a>
                                        <ul class="sub-menu collapse" id="submenu-1-4">
                                            <li class="nav-item"><a href="{{ route('front.posts.index') }}">{{ __('app.blog') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a href="contact.html" aria-label="Toggle navigation">Contact Us</a>
                                    </li> --}}
                                </ul>
                            </div> <!-- navbar collapse -->
                        </nav>