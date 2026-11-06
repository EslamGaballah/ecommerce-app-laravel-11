<x-front-layout :title="__('app.access_denied')">
    <section class="info-page error-page">
        <div class="container">
            <div class="info-page-card text-center">
                <p class="error-code">403</p>
                <h2>{{ __('app.access_denied') }}</h2>
                <p>{{ __('app.access_denied_text') }}</p>
                <div class="button">
                    <a href="{{ route('home') }}" class="btn">{{ __('app.back_to_home') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-front-layout>
