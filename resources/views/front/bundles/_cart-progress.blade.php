@foreach ($progress as $item)
    <div class="bundle-cart-note">
        <i class="lni lni-gift"></i>
        <div>
            <a href="{{ route('bundles.show', $item['bundle']) }}"><strong>{{ $item['bundle']->name }}</strong></a>
            @if ($item['tier'])
                <span>{{ __('app.bundle_discount_reached', ['discount' => $item['tier']->label]) }}</span>
            @endif
            @if ($item['next'])
                <span>{{ __('app.bundle_add_more', ['count' => $item['missing'], 'discount' => $item['next']->label]) }}</span>
            @endif
        </div>
    </div>
@endforeach
