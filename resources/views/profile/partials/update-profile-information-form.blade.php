<div class="card mb-4">
    <div class="card-header">{{ __('app.profile_information') }}</div>
    <div class="card-body">
        <p class="text-muted">{{ __('app.profile_information_text') }}</p>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success">{{ __('app.saved') }}</div>
        @endif

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route($updateRoute) }}" enctype="multipart/form-data">
            @csrf
            @method('patch')

            <div class="form-group mb-3">
                <label for="avatar">{{ __('app.profile_picture') }}</label>
                <div class="profile-avatar-row">
                    <span class="profile-avatar" id="avatar-preview">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                        @else
                            {{ mb_substr($user->name, 0, 1) }}
                        @endif
                    </span>
                    <div>
                        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                            @class(['form-control', 'is-invalid' => $errors->has('avatar')])
                            onchange="if (this.files[0]) { const img = new Image(); img.src = window.URL.createObjectURL(this.files[0]); img.alt = ''; document.getElementById('avatar-preview').replaceChildren(img); }">
                        <small class="text-muted d-block mt-1">{{ __('app.profile_picture_help') }}</small>
                        @error('avatar')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @if ($user->avatar)
                            <div class="form-check mt-2">
                                <input id="remove_avatar" type="checkbox" class="form-check-input" name="remove_avatar" value="1">
                                <label for="remove_avatar" class="form-check-label">{{ __('app.remove_profile_picture') }}</label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <x-form.field name="name" :label="__('app.name')" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-form.field name="email" type="email" :label="__('app.email')" :value="old('email', $user->email)" required autocomplete="username" />
            <x-form.field name="phone" type="tel" dir="ltr" :label="__('app.primary_phone')" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-form.field name="secondary_phone" type="tel" dir="ltr" :label="__('app.secondary_phone')" :value="old('secondary_phone', $user->secondary_phone)" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mb-3">
                    {{ __('app.email_unverified') }}
                    <button form="send-verification" class="btn btn-link p-0 align-baseline">{{ __('app.resend_verification') }}</button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">{{ __('app.verification_link_sent') }}</div>
                @endif
            @endif

            <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
        </form>
    </div>
</div>
