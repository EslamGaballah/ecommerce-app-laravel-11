{{-- ========================================= --}}
{{-- PRODUCT FORM (FULL REPLACEMENT WITH NEW VARIATIONS SUPPORT) --}}
{{-- ========================================= --}}

<div class="form-group">
    <x-form.input label="{{ __('app.name') }}"
              class="form-control-lg"
              id="product_name"
              name="name"
              :value="old('name', $product->name ?? '')" />
</div>

<div class="form-group mb-3">
    <label>{{ __('app.main_image') }}</label>
    <input type="file" name="main_image" class="form-control" accept="image/*" onchange="previewMainImage(event)">
    <div id="mainImagePreview" class="mt-2" >
        @if(isset($product) && $product->main_image)
            <img src="{{ asset('storage/' . $product->main_image) }}" width="120" class="img-thumbnail">
        @endif
    </div>
</div>

<script>
function previewMainImage(event){
    const preview = document.getElementById('mainImagePreview');
    preview.innerHTML = '';
    const file = event.target.files[0];
    if(file){
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.width = 120;
        img.classList.add('img-thumbnail');
        preview.appendChild(img);
    }
}
</script>

<div class="form-group">
    <label>{{ __('app.description') }}</label>
    <x-form.textarea name="description"
                     :value="old('description', $product->description ?? '')" />
</div>

<div class="form-group">
    <label>{{ __('app.category') }}</label>
    <select name="category_id" class="form-control form-select">
        <option value="">{{ __('app.category') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}"
                @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>{{ __('app.brand') }}</label>
    <select name="brand_id" class="form-control form-select">
        <option value="">{{ __('app.brand') }}</option>
        @foreach($brands as $brand)
            <option value="{{ $brand->id }}"
                @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>
                {{ $brand->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    @php
        $currentStatus = old('status', $product->status?->value ?? \App\Enums\ProductStatus::Draft->value);
    @endphp
    <label>{{ __('app.status') }}</label>
    <x-form.radio name="status"
                  :checked="$currentStatus"
                  :options="\App\Enums\ProductStatus::options()" />
</div>

<hr>

@php
    $productTypeValue = old('product_type', $product->product_type ?? 'simple');
@endphp
<div class="form-group">
    <label>{{ __('app.product_type') }}</label>
    <select name="product_type" id="product_type" class="form-control form-select">
        <option value="simple" @selected($productTypeValue === 'simple')>Simple</option>
        <option value="variable" @selected($productTypeValue === 'variable')>Variable</option>
    </select>
</div>

{{-- SIMPLE PRODUCT --}}
<div id="simple-section">
    <div class="row">
        <div class="col-md-4">
            <x-form.input type="number" label="Price" name="price"
                          :value="old('price', $product->price ?? '')" />
        </div>
        <div class="col-md-4">
            <x-form.input type="number" label="Compare Price" name="compare_price"
                          :value="old('compare_price', $product->compare_price ?? '')" />
        </div>
        <div class="col-md-4">
            <x-form.input type="number" label="Stock" name="stock"
                          :value="old('stock', $product->stock ?? '')" />
        </div>
    </div>

    <div class="form-group mt-3">
        <label>{{ __('app.images') }}</label>
        <input type="file" name="images[]" multiple class="form-control" accept="image/*"
               onchange="previewMultipleImages(event, 'simpleImagesPreview')">
    </div>
    <div id="simpleImagesPreview" class="row mt-3"></div>
</div>

{{-- VARIABLE PRODUCT --}}
<div id="variable-section" style="display:none">
    <hr>
    @php
    $attributesForJs = $attributes->map(function($a) {
        return [
            'id' => $a->id,
            'name' => $a->name,
            'values' => $a->attributeValues->map(function($v){
                return [
                    'id' => $v->id,
                    'value' => $v->value
                ];
            })->toArray()
        ];
    });

    $variationsForJs = $oldVariations->map(function($v){
        return [
            'id' => $v->id,
            'price' => $v->price,
            'compare_price' => $v->compare_price,
            'stock' => $v->stock,
            'sku' => $v->sku,
            'is_primary' => $v->is_primary,
            'attribute_value_ids' => $v->values->pluck('id')->toArray(),
            'images' => $v->images->map(function($img){
                return [
                    'id' => $img->id,
                    'path' => asset('storage/'.$img->image)
                ];
            })->toArray()
        ];
    });
@endphp


    

    <div class="border rounded p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label>Select Attribute</label>
                <select id="attributeSelect" class="form-control">
                    <option value="">Select</option>
                    @foreach($attributes as $attribute)
                        <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" id="addAttributeBtn" class="btn btn-primary w-100">Add Attribute</button>
            </div>
        </div>
        <div id="attributesContainer" class="mt-3"></div>
        <button type="button" id="generateVariants" class="btn btn-primary mt-3">
            Generate Variants
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered d-none" id="variantTable">
            <thead><tr id="variantHeader"></tr></thead>
            <tbody id="variantBody"></tbody>
        </table>
    </div>
</div>

<div class="form-group mt-4">
    <button type="submit" class="btn btn-primary">{{ __($button_label ?? 'Save') }}</button>
</div>

@push('style')
<style>

.img-box{
    position:relative;
    margin:5px;
}

.img-box img{
    width:70px;
    height:70px;
    object-fit:cover;
    border:1px solid #ddd;
     display: block;
}

.img-box button{
    position:absolute;
    top:-5px;
    right:-5px;
    padding:2px 6px;
    cursor: pointer;
}
</style>

@endpush

@push('script')

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    window.__ATTRIBUTES__ = @json($attributesForJs);
    window.__OLD_VARIATIONS__ = @json($variationsForJs);
</script>

    @vite('resources/js/dashboard/product-page.js')

@endpush