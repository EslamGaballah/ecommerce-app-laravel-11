<x-guest-layout :title="__('app.forgot_password')" :subtitle="__('app.forgot_password_text')">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <x-form.field name="email" type="email" :label="__('app.email')" :value="old('email')" required autofocus />

        <div class="button">
            <button type="submit" class="btn w-100">{{ __('app.send_reset_link') }}</button>
        </div>

        <p class="outer-link mt-3 mb-0"><a href="{{ route('login') }}">{{ __('app.login') }}</a></p>
    </form>
</x-guest-layout>
