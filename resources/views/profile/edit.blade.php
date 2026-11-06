<x-front-layout :title="__('app.profile')">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/account.css') }}" />
    @endpush

    <x-slot:breadcrumb>
        <div class="breadcrumbs">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6 col-12">
                        <h1 class="page-title">{{ __('app.profile') }}</h1>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <ul class="breadcrumb-nav">
                            <li><a href="{{ route('home') }}"><i class="lni lni-home"></i> {{ __('app.home') }}</a></li>
                            <li>{{ __('app.profile') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:breadcrumb>

    <div class="section account-profile">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-12">
                    @include('profile.partials.update-profile-information-form', ['updateRoute' => 'profile.update'])
                    @include('profile.partials.update-password-form')
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-front-layout>
