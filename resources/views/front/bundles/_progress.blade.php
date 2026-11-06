@php
    $target = $progress['next']?->min_quantity ?? $progress['quantity'];
    $percent = $target > 0 ? min(100, (int) round($progress['quantity'] / $target * 100)) : 0;
@endphp

<div class="bundle-progress-bar" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
    <span style="width: {{ $percent }}%"></span>
</div>

<p class="mb-1">
    <strong>{{ __('app.bundle_in_cart', ['count' => $progress['quantity']]) }}</strong>
    @if ($progress['tier'])
        — {{ __('app.bundle_discount_reached', ['discount' => $progress['tier']->label]) }}
    @endif
</p>

@if ($progress['next'])
    <p class="mb-0 text-muted">{{ __('app.bundle_add_more', ['count' => $progress['missing'], 'discount' => $progress['next']->label]) }}</p>
@elseif ($progress['tier'])
    <p class="mb-0 text-muted">{{ __('app.bundle_best_tier') }}</p>
@endif
