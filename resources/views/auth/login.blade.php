<x-guest-layout :title="__('app.login')" :subtitle="__('app.login_subtitle')">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <x-form.field name="email" type="email" :label="__('app.email')" :value="old('email')" required autofocus autocomplete="username" />
        <x-form.field name="password" type="password" :label="__('app.password')" required autocomplete="current-password" />

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label for="remember_me" class="form-check-label">{{ __('app.remember_me') }}</label>
            </div>
            <a href="{{ route('password.request') }}">{{ __('app.forgot_password') }}</a>
        </div>

        <div class="button">
            <button type="submit" class="btn w-100">{{ __('app.login') }}</button>
        </div>

                @if (Setting::get('registration_enabled'))
            <p class="outer-link mt-3 mb-0">
                {{ __('app.no_account') }} <a href="{{ route('register') }}">{{ __('app.register') }}</a>
            </p>
        @endif
    </form>
</x-guest-layout>
