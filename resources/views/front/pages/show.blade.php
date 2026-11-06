<x-front-layout :title="$page['title']">
    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => $page['title']])
    </x-slot:breadcrumb>

    <section class="info-page">
        <div class="container">
            <div class="info-page-card">
                <p class="info-page-intro">{{ $page['intro'] }}</p>

                @if ($updatedAt)
                    <p class="info-page-date">{{ __('pages.last_updated', ['date' => $updatedAt]) }}</p>
                @endif

                @foreach ($page['sections'] as $section)
                    <h2>{{ $section['heading'] }}</h2>

                    @isset($section['body'])
                        <p>{{ $section['body'] }}</p>
                    @endisset

                    @isset($section['items'])
                        <ul>
                            @foreach ($section['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endisset
                @endforeach

                <div class="info-page-help">
                    <strong>{{ __('pages.still_have_questions') }}</strong>
                    <a href="{{ route('contact') }}">{{ __('pages.contact_us_link') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-front-layout>
