<x-guest-layout :title="__('app.confirm_password')" :subtitle="__('app.secure_area_text')">
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <x-form.field name="password" type="password" :label="__('app.password')" required autocomplete="current-password" />

        <div class="button">
            <button type="submit" class="btn w-100">{{ __('app.confirm') }}</button>
        </div>
    </form>
</x-guest-layout>
