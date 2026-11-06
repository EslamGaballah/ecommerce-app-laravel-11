
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
        <x-form.input label="{{ __('app.name_ar') }}" class="form-control-lg" role="input" name="name_ar" :value="old('name_ar', $tag->name_ar)" />
    </div>

    <div class="form-group">
        <x-form.input label="{{ __('app.name_en') }}" class="form-control-lg" role="input" name="name_en" :value="old('name_en', $tag->name_en)" />
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ $button_label ?? __('app.save') }}</button>
    </div>