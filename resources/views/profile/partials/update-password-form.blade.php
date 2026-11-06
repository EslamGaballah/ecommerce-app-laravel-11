<div class="card mb-4">
    <div class="card-header">{{ __('app.update_password') }}</div>
    <div class="card-body">
        <p class="text-muted">{{ __('app.update_password_text') }}</p>

        @if (session('status') === 'password-updated')
            <div class="alert alert-success">{{ __('app.saved') }}</div>
        @endif

        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <x-form.field name="current_password" id="update_password_current_password" type="password" bag="updatePassword" :label="__('app.current_password')" autocomplete="current-password" />
            <x-form.field name="password" id="update_password_password" type="password" bag="updatePassword" :label="__('app.new_password')" autocomplete="new-password" />
            <x-form.field name="password_confirmation" id="update_password_password_confirmation" type="password" bag="updatePassword" :label="__('app.confirm_password')" autocomplete="new-password" />

            <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
        </form>
    </div>
</div>
