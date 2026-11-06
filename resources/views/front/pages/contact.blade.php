<x-front-layout :title="__('app.contact')">
    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => __('app.contact')])
    </x-slot:breadcrumb>

    <section class="info-page">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-12">
                    <div class="info-page-card contact-details">
                        <h2>{{ __('app.contact_details') }}</h2>
                        <p>{{ __('app.contact_intro') }}</p>

                        <ul>
                            @if (Setting::get('phone'))
                                <li>
                                    <i class="lni lni-phone"></i>
                                    <div>
                                        <strong>{{ __('app.phone') }}</strong>
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', Setting::get('phone')) }}" dir="ltr">{{ Setting::get('phone') }}</a>
                                    </div>
                                </li>
                            @endif
                            @if (Setting::get('whatsapp'))
                                <li>
                                    <i class="lni lni-whatsapp"></i>
                                    <div>
                                        <strong>{{ __('app.whatsapp') }}</strong>
                                        <a href="https://wa.me/{{ Setting::get('whatsapp') }}" target="_blank" rel="noopener" dir="ltr">+{{ Setting::get('whatsapp') }}</a>
                                    </div>
                                </li>
                            @endif
                            @if (Setting::get('contact_email'))
                                <li>
                                    <i class="lni lni-envelope"></i>
                                    <div>
                                        <strong>{{ __('app.email') }}</strong>
                                        <a href="mailto:{{ Setting::get('contact_email') }}" dir="ltr">{{ Setting::get('contact_email') }}</a>
                                    </div>
                                </li>
                            @endif
                            @if (Setting::get('address'))
                                <li>
                                    <i class="lni lni-map-marker"></i>
                                    <div>
                                        <strong>{{ __('app.address') }}</strong>
                                        <span>{{ Setting::get('address') }}</span>
                                    </div>
                                </li>
                            @endif
                            @if (Setting::get('working_hours'))
                                <li>
                                    <i class="lni lni-timer"></i>
                                    <div>
                                        <strong>{{ __('app.working_hours') }}</strong>
                                        <span>{{ Setting::get('working_hours') }}</span>
                                    </div>
                                </li>
                            @endif
                        </ul>

                        @if (App\Models\Setting::hasSocialLinks())
                            <h2 class="mt-4">{{ __('app.follow_us') }}</h2>
                            <ul class="contact-social">
                                @include('partials.social-links')
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8 col-12">
                    <div class="info-page-card">
                        <h2>{{ __('app.send_us_message') }}</h2>

                        <x-alert type="success" />

                        <form action="{{ route('contact.store') }}" method="POST" class="contact-form">
                            @csrf

                            {{-- Hidden from people; only bots fill it in. --}}
                            <div class="contact-website" aria-hidden="true">
                                <label for="website">Website</label>
                                <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <x-form.field name="name" :label="__('app.name')" :value="old('name', auth()->user()?->name)" required maxlength="100" autocomplete="name" />
                                </div>
                                <div class="col-md-6 col-12">
                                    <x-form.field name="email" type="email" :label="__('app.email')" :value="old('email', auth()->user()?->email)" required maxlength="255" autocomplete="email" />
                                </div>
                                <div class="col-md-6 col-12">
                                    <x-form.field name="phone" type="tel" dir="ltr" :label="__('app.phone_optional')" :value="old('phone', auth()->user()?->phone)" maxlength="30" autocomplete="tel" />
                                </div>
                                <div class="col-md-6 col-12">
                                    <x-form.field name="subject" :label="__('app.subject')" :value="old('subject')" required maxlength="150" />
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="message">{{ __('app.your_message') }}</label>
                                <textarea id="message" name="message" rows="6" required minlength="10" maxlength="5000"
                                    @class(['form-control', 'is-invalid' => $errors->has('message')])>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="button">
                                <button type="submit" class="btn">{{ __('app.send_message') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-front-layout>
