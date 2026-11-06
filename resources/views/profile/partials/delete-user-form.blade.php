<div class="card mb-4">
    <div class="card-header">{{ __('app.delete_account') }}</div>
    <div class="card-body">
        <p class="text-muted">{{ __('app.delete_account_text') }}</p>

        <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm(@js(__('app.are_you_sure')))">
            @csrf
            @method('delete')

            <x-form.field name="password" id="delete_account_password" type="password" bag="userDeletion" :label="__('app.password')" autocomplete="current-password" />

            <button type="submit" class="btn btn-danger">{{ __('app.delete_account') }}</button>
        </form>
    </div>
</div>
