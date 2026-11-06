<x-front-layout :title="__('app.pay_for_order')">
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-12">
                    <div class="alert alert-warning">
                        <strong>{{ __('app.test_mode') }}</strong> {{ __('app.test_mode_text') }}
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @error('card')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="card">
                        <div class="card-body p-4">
                            <h4 class="mb-3">{{ __('app.pay_for_order') }}</h4>
                            <p class="mb-1">{{ __('app.order_number') }}: <strong>{{ $order->number }}</strong></p>
                            <p class="mb-4">{{ __('app.total') }}: <strong>{{ Currency::format($order->total) }}</strong></p>

                            <form action="{{ route('front.payments.store', $order) }}" method="post">
                                @csrf

                                <h6 class="mb-2">{{ __('app.choose_test_card') }}</h6>
                                @foreach ($cards as $key => $card)
                                    <div class="form-check mb-2">
                                        <input type="radio" class="form-check-input" name="card" id="card-{{ $key }}" value="{{ $key }}" required @checked($loop->first)>
                                        <label class="form-check-label" for="card-{{ $key }}">
                                            •••• {{ $card['last4'] }} — {{ __('app.test_card_'.$key) }}
                                        </label>
                                    </div>
                                @endforeach

                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    {{ __('app.pay_amount', ['amount' => Currency::format($order->total)]) }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-front-layout>
