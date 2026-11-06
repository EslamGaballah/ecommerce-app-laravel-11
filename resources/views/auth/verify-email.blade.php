<x-guest-layout :title="__('app.verify_email')" :subtitle="__('app.verify_email_text')">
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">{{ __('app.verification_link_sent') }}</div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="button">
                <button type="submit" class="btn">{{ __('app.resend_verification') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link">{{ __('app.logout') }}</button>
        </form>
    </div>
</x-guest-layout>
