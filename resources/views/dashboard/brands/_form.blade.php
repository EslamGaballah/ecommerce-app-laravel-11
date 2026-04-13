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

{{-- start form --}}
<div class="form-group">
    <x-form.input 
        label="الاسم بالعربية" 
        class="form-control-lg" 
        name="name_ar" 
        :value="old('name_ar', $brand->name_ar ?? '')" 
    />
</div>

<div class="form-group">
    <x-form.input 
        label="NAME_EN" 
        class="form-control-lg" 
        name="name_en" 
        :value="old('name_en', $brand->name_en ?? '')" 
    />
</div>

<div class="form-group">
    <label>{{ __('app.description') }}</label>
    <x-form.textarea 
        name="description" 
        :value="old('description', $brand->description ?? '')" 
    />
</div>

{{-- image (اختياري) --}}
{{-- 
<div class="form-group">
    <x-form.label id="image">Image</x-form.label>
    <x-form.input type="file" name="image" accept="image/*" />

    @if(!empty($brand->image))
        <img src="{{ asset('storage/' . $brand->image) }}" height="60">
    @endif
</div>
--}}

<div class="form-group mt-3">
    <button type="submit" class="btn btn-primary">
        {{ __($button_label ?? 'app.save') }}
    </button>
</div>