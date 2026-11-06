<x-front-layout :title="__('app.order_confirmed')">

    <div class="container text-center py-5">
        <h2>{{ __('app.order_confirmed') }}</h2>

        <p>{{ __('app.order_number') }}: #{{ $order->number }}</p>

        <p>{{ __('app.payment_method') }}: {{ $order->payment_method->label() }}</p>

        <p>{{ __('app.payment_status') }}: {{ $order->payment_status->label() }}</p>

        @if ($order->isPayable() && ! app()->isProduction())
            <a href="{{ route('front.payments.show', $order) }}" class="btn btn-primary mt-3">{{ __('app.pay_now') }}</a>
        @else
            <p>{{ __('app.order_contact_soon') }}</p>
        @endif

        <a href="{{ route('home') }}" class="btn btn-outline-primary mt-3">{{ __('app.back_to_home') }}</a>
    </div>

</x-front-layout>
