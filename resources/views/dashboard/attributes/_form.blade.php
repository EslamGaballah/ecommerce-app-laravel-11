
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
        <x-form.input label="{{ __('app.name') }}" class="form-control-lg" role="input" name="name" :value="$attribute->name" />
    </div>
    
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __( $button_label ?? 'app.save') }}</button>
    </div>