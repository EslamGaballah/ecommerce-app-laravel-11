<x-front-layout :title="$page['title']">
    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => $page['title']])
    </x-slot:breadcrumb>

    <section class="info-page">
        <div class="container">
            <div class="info-page-card">
                <p class="info-page-intro">{{ $page['intro'] }}</p>

                <div class="accordion" id="faq">
                    @foreach ($page['items'] as $index => $item)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq-heading-{{ $index }}">
                                <button @class(['accordion-button', 'collapsed' => $index > 0]) type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq-answer-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-controls="faq-answer-{{ $index }}">{{ $item['q'] }}</button>
                            </h2>
                            <div id="faq-answer-{{ $index }}" @class(['accordion-collapse', 'collapse', 'show' => $index === 0])
                                aria-labelledby="faq-heading-{{ $index }}" data-bs-parent="#faq">
                                <div class="accordion-body">{{ $item['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="info-page-help">
                    <strong>{{ __('pages.still_have_questions') }}</strong>
                    <a href="{{ route('contact') }}">{{ __('pages.contact_us_link') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-front-layout>
