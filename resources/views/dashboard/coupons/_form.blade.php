@if($errors->any())
    <div class="alert alert-danger">
        <h3>{{ __('app.error_occurred') }}</h3>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group mb-3">
    <x-form.input 
        label="{{ __('app.coupon_code') }}" 
        class="form-control-lg" 
        role="input" 
        name="code"
        :value="old('code', $coupon->code ?? '')" 
    />
</div>

<div class="form-group mb-3">
    <label for="type">{{ __('app.type') }}</label>
    <select name="type" class="form-control form-select">
        <option value="fixed" @selected(old('type', $coupon->type ?? '')=='fixed')>
            {{ __('app.fixed_amount') }}
        </option>
        <option value="percent" @selected(old('type', $coupon->type ?? '')=='percent')>
            {{ __('app.percentage') }}
        </option>
        <option value="free_shipping" @selected(old('type', $coupon->type ?? '')=='free_shipping')>
            {{ __('app.free_shipping') }}
        </option>
    </select>
</div>

<div class="form-group mb-3">
    <x-form.input 
        label="{{ __('app.value') }}" 
        type="number" 
        step="0.01" 
        name="value"
        :value="old('value', $coupon->value ?? '')" 
    />
</div>

<div class="form-group mb-3">
    <x-form.input
        label="{{ __('app.min_order_amount') }}"
        type="number"
        step="0.01"
        name="min_order_amount"
        :value="old('min_order_amount', $coupon->min_order_amount ?? '')"
    />
</div>

<div class="form-group mb-3">
    <x-form.input
        label="{{ __('app.usage_limit') }}" 
        type="number" 
        name="usage_limit"
        :value="old('usage_limit', $coupon->usage_limit ?? '')" 
    />
    <small class="form-text text-muted">
        {{ __('app.leave_empty_unlimited_usage') }}
    </small>
</div>

<div class="form-group mb-3">
    <label for="expiry_date">{{ __('app.expiry_date') }}</label>
    <x-form.input 
        type="date" 
        name="expires_at"
        :value="old('expires_at', ($coupon->expires_at ?? null)?->format('Y-m-d'))" 
    />
    <small class="form-text text-muted">
        {{ __('app.leave_empty_no_expiration') }}
    </small>
</div>

<div class="form-group mb-3">
    <label>{{ __('app.status') }}</label>
    <div>
        <x-form.radio 
            name="is_active"
            :value="(int) ($coupon->is_active ?? 1)"
            :options="[
                1 => __('app.active'),
                0 => __('app.inactive')
            ]" 
        />
    </div>
</div>

<div class="form-group mb-3">
    <button type="submit" class="btn btn-primary">
        {{ $button_label ?? __('app.save') }}
    </button>
</div>