<x-front-layout :title="__('app.page_not_found')">
    <section class="info-page error-page">
        <div class="container">
            <div class="info-page-card text-center">
                <p class="error-code">404</p>
                <h2>{{ __('app.page_not_found') }}</h2>
                <p>{{ __('app.page_not_found_text') }}</p>
                <div class="button">
                    <a href="{{ route('home') }}" class="btn">{{ __('app.back_to_home') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-front-layout>
