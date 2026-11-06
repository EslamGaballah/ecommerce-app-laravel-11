
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

     <div class="form-group">
    <label for="">{{ __ ('app.attributes')}}</label>
    <select name="attribute_id" class="form-control form-select">
        <option value="">{{ __ ('app.attributes')}}</option>
        @foreach ( $attributes as $attribute )
        <option value="{{ $attribute->id }}" @selected(old('attribute_id', $attribute_value->attribute_id) == $attribute->id)>{{ $attribute->name }}</option>
        @endforeach
    </select>


    <div class="form-group">
        <x-form.input label="{{ __('app.value') }}  {{ __('app.comma_separated_hint') }}" class="form-control-lg" role="input" name="value" :value="$attribute_value->value" />
    </div>


</div>


    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __( $button_label ?? 'app.save') }}</button>
    </div>
