<x-front-layout :title="$title">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/account.css') }}" />
    @endpush

    <x-slot:breadcrumb>
        <div class="breadcrumbs">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6 col-12">
                        <h1 class="page-title">{{ $title }}</h1>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <ul class="breadcrumb-nav">
                            <li><a href="{{ route('home') }}"><i class="lni lni-home"></i> {{ __('app.home') }}</a></li>
                            <li>{{ $title }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:breadcrumb>

    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">
                    <div class="card login-form">
                        <div class="card-body">
                            <div class="title">
                                <h3>{{ $title }}</h3>
                                @if ($subtitle)
                                    <p>{{ $subtitle }}</p>
                                @endif
                            </div>

                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-front-layout>
