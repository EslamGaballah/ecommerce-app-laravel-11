
@if($errors->any())
<div class="alert alert-danger">
    <h3>Error Occured!</h3>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

    <div class="form-group">
        <x-form.input label="الاسم بالعربية" class="form-control-lg" role="input" name="name_ar" :value="old('name_ar', $governorate->name_ar)" />
    </div>
    <div class="form-group">
        <x-form.input label="NAME_EN" class="form-control-lg" role="input" name="name_en" :value="old('name_en', $governorate->name_en)" />
    </div>
    <div class="form-group">
    <x-form.input label="{{ __ ('app.shipping_price')}}" name="shipping_price" :value="old('shipping_price',$governorate->shipping_price)" />
    </div>
    <div class="form-group">
        <label class="form-label">{{ __ ('app.delivery_days')}}</label>
        <input type="number" 
            name="delivery_days"
            class="form-control"
            value="{{ old('delivery_days', $governorate->delivery_days ?? '') }}"
            min="1"
            required>
    </div>
    
    <div class="form-group">
        <label for="">{{ __('app.status') }}</label>
        <div>
            <x-form.radio 
                name="is_active" 
                :checked="old('is_active', $governorate->is_active)" 
                :options="[
                    1 => __('app.active'), 
                    0 => __('app.archived')
                ]"
            />
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __( $button_label ?? 'app.save') }}</button>
    </div>