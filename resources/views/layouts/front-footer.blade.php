 <footer class="footer">
        <!-- Start Footer Top -->
        {{-- <div class="footer-top">
            <div class="container">
                <div class="inner-content">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-12">
                            <div class="footer-logo">
                                <a href="index.html">
                                    <img src="{{ asset('assets/images/logo/white-logo.svg') }}" alt="#">
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-9 col-md-8 col-12">
                            <div class="footer-newsletter">
                                <h4 class="title">
                                    Subscribe to our Newsletter
                                    <span>Get all the latest information, Sales and Offers.</span>
                                </h4>
                                <div class="newsletter-form-head">
                                    <form action="#" method="get" target="_blank" class="newsletter-form">
                                        <input name="EMAIL" placeholder="Email address here..." type="email">
                                        <div class="button">
                                            <button class="btn">Subscribe<span class="dir-part"></span></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- End Footer Top -->
        <!-- Start Footer Middle -->
        {{-- <div class="footer-middle">
            <div class="container">
                <div class="bottom-inner">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Single Widget -->
                            <div class="single-footer f-contact">
                                <h3>Get In Touch With Us</h3>
                                <p class="phone">Phone: +1 (900) 33 169 7720</p>
                                <ul>
                                    <li><span>Monday-Friday: </span> 9.00 am - 8.00 pm</li>
                                    <li><span>Saturday: </span> 10.00 am - 6.00 pm</li>
                                </ul>
                                <p class="mail">
                                    <a href="mailto:support@shopgrids.com">support@shopgrids.com</a>
                                </p>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Single Widget -->
                            <div class="single-footer our-app">
                                <h3>Our Mobile App</h3>
                                <ul class="app-btn">
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="lni lni-apple"></i>
                                            <span class="small-title">Download on the</span>
                                            <span class="big-title">App Store</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="lni lni-play-store"></i>
                                            <span class="small-title">Download on the</span>
                                            <span class="big-title">Google Play</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Single Widget -->
                            <div class="single-footer f-link">
                                <h3>Information</h3>
                                <ul>
                                    <li><a href="javascript:void(0)">About Us</a></li>
                                    <li><a href="javascript:void(0)">Contact Us</a></li>
                                    <li><a href="javascript:void(0)">Downloads</a></li>
                                    <li><a href="javascript:void(0)">Sitemap</a></li>
                                    <li><a href="javascript:void(0)">FAQs Page</a></li>
                                </ul>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Single Widget -->
                            <div class="single-footer f-link">
                                <h3>Shop Departments</h3>
                                <ul>
                                    <li><a href="javascript:void(0)">Computers & Accessories</a></li>
                                    <li><a href="javascript:void(0)">Smartphones & Tablets</a></li>
                                    <li><a href="javascript:void(0)">TV, Video & Audio</a></li>
                                    <li><a href="javascript:void(0)">Cameras, Photo & Video</a></li>
                                    <li><a href="javascript:void(0)">Headphones</a></li>
                                </ul>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- End Footer Middle -->
        <!-- Start Footer Links -->
        <div class="footer-links">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-12 footer-links-column">
                        <h3>{{ Setting::get('site_name') }}</h3>
                        <p>{{ __('pages.about.intro', ['site' => Setting::get('site_name')]) }}</p>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 footer-links-column">
                        <h3>{{ __('app.shop') }}</h3>
                        <ul>
                            <li><a href="{{ route('products.index') }}">{{ __('app.products') }}</a></li>
                            <li><a href="{{ route('front.posts.index') }}">{{ __('app.blog') }}</a></li>
                            <li><a href="{{ route('cart.index') }}">{{ __('app.cart') }}</a></li>
                            <li><a href="{{ route('front.orders.index') }}">{{ __('app.my_orders') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6 footer-links-column">
                        <h3>{{ __('app.information') }}</h3>
                        <ul>
                            <li><a href="{{ route('pages.about') }}">{{ __('app.about') }}</a></li>
                            <li><a href="{{ route('contact') }}">{{ __('app.contact') }}</a></li>
                            <li><a href="{{ route('pages.faq') }}">{{ __('pages.faq.title') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 col-12 footer-links-column">
                        <h3>{{ __('app.policies') }}</h3>
                        <ul>
                            <li><a href="{{ route('pages.privacy') }}">{{ __('pages.privacy.title') }}</a></li>
                            <li><a href="{{ route('pages.terms') }}">{{ __('pages.terms.title') }}</a></li>
                            <li><a href="{{ route('pages.shipping') }}">{{ __('pages.shipping.title') }}</a></li>
                            <li><a href="{{ route('pages.returns') }}">{{ __('pages.returns.title') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Footer Links -->

        <!-- Start Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="inner-content">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-12">
                            <div class="payment-gateway">
                                <span>{{ __('app.we_accept') }}:</span>
                                {{ collect(App\Enums\PaymentMethod::enabled())->map->label()->implode(' · ') }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            <div class="copyright">
                                <p>{{ __('app.copyright', ['year' => date('Y')]) }}</p>
                                <p>{{ __('app.developed_by') }}</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            @if (Setting::get('show_social_footer') && App\Models\Setting::hasSocialLinks())
                                <ul class="socila">
                                <li>
                                    <span>{{ __('app.follow_us') }}:</span>
                                </li>
                                @include('partials.social-links')
                            </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Footer Bottom -->
    </footer>