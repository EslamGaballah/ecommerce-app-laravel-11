<x-guest-layout :title="__('app.register')" :subtitle="__('app.register_subtitle')">
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <x-form.field name="name" :label="__('app.name')" :value="old('name')" required autofocus autocomplete="name" />
        <x-form.field name="email" type="email" :label="__('app.email')" :value="old('email')" required autocomplete="username" />
        <x-form.field name="password" type="password" :label="__('app.password')" required autocomplete="new-password" />
        <x-form.field name="password_confirmation" type="password" :label="__('app.confirm_password')" required autocomplete="new-password" />

        <p class="small text-muted mb-3">
            {{ __('pages.agree_terms') }}
            <a href="{{ route('pages.terms') }}">{{ __('pages.terms.title') }}</a>
            {{ __('pages.and') }}
            <a href="{{ route('pages.privacy') }}">{{ __('pages.privacy.title') }}</a>.
        </p>

        <div class="button">
            <button type="submit" class="btn w-100">{{ __('app.register') }}</button>
        </div>

        <p class="outer-link mt-3 mb-0">
            {{ __('app.already_registered') }} <a href="{{ route('login') }}">{{ __('app.login') }}</a>
        </p>
    </form>
</x-guest-layout>
