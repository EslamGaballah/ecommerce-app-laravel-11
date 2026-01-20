
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
        <x-form.input label="{{ __('app.name') }}" class="form-control-lg" role="input" name="name" :value="$governorate->name" />
    </div>
    <div class="form-group">
    <x-form.input label="{{ __ ('app.shipping_price')}}" name="price" :value="$governorate->shipping_price" />
    </div>
    <div class="form-group">
        <label class="form-label">مدة التوصيل (بالأيام)</label>
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
            <x-form.radio name="is_active" :checked="$governorate->status" 
                :options="[
                    'active' =>  __('app.active') , 
                    'archived' =>  __('app.archived') 
                    ]" />
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __( $button_label ?? 'app.save') }}</button>
    </div>