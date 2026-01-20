
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
        <x-form.input label="{{ __('app.name') }}" class="form-control-lg" role="input" name="name" :value="$category->name" />
    </div>
    <div class="form-group">
        <label for="">{{ __('app.parent') }}</label>
         <select name="parent_id" class="form-control form-select">
            <option value="">{{ __('app.parent') }}</option>
            @foreach($parents as $parent)
            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="">{{ __('app.description') }}</label>
        <x-form.textarea name="description" :value="$category->description" />
    </div>
    {{-- <div class="form-group">
        <x-form.label id="image">Image</x-form.label>
        <x-form.input type="file" name="image" accept="image/*" />
        @if ($category->image)
        <img src="{{ asset('storage/' . $category->image) }}" alt="" height="60">
        @endif
    </div> --}}
    <div class="form-group">
        <label for="">{{ __('app.status') }}</label>
        <div>
            <x-form.radio name="status" :checked="$category->status" 
                :options="[
                    'active' =>  __('app.active') , 
                    'archived' =>  __('app.archived') 
                    ]" />
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __( $button_label ?? 'app.save') }}</button>
    </div>