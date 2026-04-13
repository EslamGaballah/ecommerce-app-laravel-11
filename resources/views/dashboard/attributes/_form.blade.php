
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
        <x-form.input label="الاسم بالعربية" class="form-control-lg" role="input" name="name_ar" 
            :value="old('name_ar', $attribute->name_ar)" /> 
    </div>
    <div class="form-group">
        <x-form.input label="NAME_EN" class="form-control-lg" role="input" name="name_en" 
             :value="old('name_en', $attribute->name_en)" />
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __( $button_label ?? 'app.save') }}</button>
    </div>