<x-guest-layout :title="__('app.reset_password')">
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form.field name="email" type="email" :label="__('app.email')" :value="old('email', $request->email)" required autofocus autocomplete="username" />
        <x-form.field name="password" type="password" :label="__('app.new_password')" required autocomplete="new-password" />
        <x-form.field name="password_confirmation" type="password" :label="__('app.confirm_password')" required autocomplete="new-password" />

        <div class="button">
            <button type="submit" class="btn w-100">{{ __('app.reset_password') }}</button>
        </div>
    </form>
</x-guest-layout>
