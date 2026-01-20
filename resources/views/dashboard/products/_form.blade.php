

<div class="form-group">
    {{-- <label for=""> {{ __ ('app.products')}} </label> --}}
    <x-form.input label="{{ __ ('app.name')}}" class="form-control-lg" role="input" name="name" :value="$product->name" />
</div>
<div class="form-group">
    <label for="">{{ __ ('app.category')}}</label>
    <select name="category_id" class="form-control form-select">
        <option value="">{{ __ ('app.category')}}</option>
        @foreach(App\Models\Category::all() as $category)
        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="" required >{{ __ ('app.description')}}</label>
    <x-form.textarea name="description" :value="$product->description" />
</div>
{{-- <div class="form-group">
    <label for="">Quantity</label>
    <x-form.textarea name="quantity" :value="$product->quantity" />
</div> --}}
<div class="form-group">
    <label for="">{{ __ ('app.stock')}}</label>
    <x-form.input label="{{ __ ('app.stock')}}" name="quantity" :value="$product->quantity" />
</div>

<div class="form-group">
    <x-form.input label="{{ __ ('app.price')}}" name="price" :value="$product->price" />
</div>

<div class="form-group">
    <x-form.input label="{{ __ ('app.compare_price')}}" name="compare_price" :value="$product->compare_price" />
</div>

{{-- upload new images --}}
<div class="form-group">
    <x-form.label id="image">{{ __ ('app.images')}}</x-form.label>
    <x-form.input 
        type="file" 
        name="image[]" 
        accept="image/*"  
        multiple
        id="imagesInput"
    />
    {{-- alt new images --}}
    <div id="new-images-alt"></div>

    @if($product && $product->images->count())
        <div class="mt-3">
            @foreach($product->images as $index => $image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $image->image) }}" height="60">

                    <label>Alt</label>
                    <x-form.input
                        name="existing_image_alt[{{ $image->id }}]"
                        :value="old('existing_image_alt.' . $image->id, $image->alt)"
                    />
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="form-group">
    <label for="">{{ __ ('app.status')}}</label>
    <div>
        <x-form.radio name="status" :checked="$product->status" 
            :options="[
                'active' =>  __ ('app.active'),
                'draft' => __ ('app.draft'),
                'archived' => __ ('app.archived'),
                  ]" 
                />
    </div>
</div>
<div class="form-group">
    <button type="submit" class="btn btn-primary">{{ __($button_label ?? 'Save') }}</button>
</div>

@push('styles')
    <link href="{{ asset('css/tagify.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('js/tagify.min.js') }}"></script>
<script src="{{ asset('js/tagify.polyfills.min.js') }}"></script>
<script>
    var inputElm = document.querySelector('[name=tags]'),
    tagify = new Tagify (inputElm);
</script>
@endpush

@push('script')
<script>
    document.getElementById('imagesInput').addEventListener('change', function () {
        const container = document.getElementById('new-images-alt');
        container.innerHTML = '';

        Array.from(this.files).forEach((file) => {
            container.innerHTML += `
                <div class="mt-2">
                    <label>Alt for ${file.name}</label>
                    <input
                        type="text"
                        name="image_alt[]"
                        class="form-control"
                        placeholder="Alt text"
                    >
                </div>
            `;
        });
    });
</script>
@endpush