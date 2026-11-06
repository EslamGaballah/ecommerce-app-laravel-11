<x-front-layout :title="__('app.notifications')">
    <x-slot:breadcrumb>
        @include('partials.page-breadcrumb', ['title' => __('app.notifications')])
    </x-slot:breadcrumb>

    <section class="info-page">
        <div class="container">
            @include('notifications._list')
        </div>
    </section>
</x-front-layout>
