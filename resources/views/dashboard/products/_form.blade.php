@if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('app.product_form_errors') }}</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $productTypeValue = old('product_type', $product->product_type?->value ?? 'simple');
    $currentStatus = old('status', $product->status?->value ?? \App\Enums\ProductStatus::Draft->value);
@endphp

<div class="row product-form">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">{{ __('app.product_basic_info') }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <x-form.input label="{{ __('app.name_ar') }}"
                                      id="product_name_ar"
                                      name="name_ar"
                                      required
                                      maxlength="255"
                                      :value="old('name_ar', $product?->name_ar ?? '')" />
                    </div>
                    <div class="col-md-6 form-group">
                        <x-form.input label="{{ __('app.name_en') }}"
                                      id="product_name_en"
                                      name="name_en"
                                      required
                                      maxlength="255"
                                      :value="old('name_en', $product?->name_en ?? '')" />
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="description">{{ __('app.description') }}</label>
                    <textarea id="description" name="description" rows="5" maxlength="5000"
                        @class(['form-control', 'is-invalid' => $errors->has('description')])>{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">{{ __('app.product_pricing_stock') }}</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="product_type">{{ __('app.product_type') }}</label>
                    <select name="product_type" id="product_type" class="form-control">
                        <option value="simple" @selected($productTypeValue === 'simple')>{{ __('app.simple') }}</option>
                        <option value="variable" @selected($productTypeValue === 'variable')>{{ __('app.variable') }}</option>
                    </select>
                    <small class="text-muted">{{ __('app.product_type_help') }}</small>
                </div>

                {{-- SIMPLE PRODUCT --}}
                <div id="simple-section" style="{{ $productTypeValue === 'variable' ? 'display:none' : 'display:block' }}">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <x-form.input type="number" step="0.01" min="0" label="{{ __('app.price') }}" name="price" id="product_price"
                                          :value="old('price', $product->price ?? '')" />
                        </div>
                        <div class="col-md-4 form-group">
                            <x-form.input type="number" step="0.01" min="0" label="{{ __('app.compare_price') }}" name="compare_price" id="product_compare_price"
                                          :value="old('compare_price', $product->compare_price ?? '')" />
                            <small class="text-muted" id="product_discount_hint"></small>
                        </div>
                        <div class="col-md-4 form-group">
                            <x-form.input type="number" step="1" min="0" label="{{ __('app.stock') }}" name="stock"
                                          :value="old('stock', $product->stock ?? '')" />
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="gallery">{{ __('app.gallery') }}</label>
                        <input type="file"
                               id="gallery"
                               name="gallery[]"
                               multiple
                               class="form-control"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="handleGalleryUpload(event)">
                        <small class="text-muted">{{ __('app.product_images_help') }}</small>
                    </div>

                    <div id="galleryPreview" class="d-flex flex-wrap mt-3"></div>
                </div>

{{-- VARIABLE PRODUCT --}}
<div id="variable-section" style="{{ $productTypeValue === 'variable' ? 'display:block' : 'display:none' }}">
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

    // After a validation error the table shows what the admin sent, not what is saved.
    // Browsers never refill file inputs, so chosen photos have to be picked again.
    $variationsForJs = old('variations')
        ? collect(old('variations'))->values()->map(fn ($v, $index) => [
            'id' => $v['id'] ?? null,
            'price' => $v['price'] ?? '',
            'compare_price' => $v['compare_price'] ?? '',
            'stock' => $v['stock'] ?? '',
            'sku' => $v['sku'] ?? '',
            'is_primary' => (string) old('primary') === (string) $index,
            'attribute_value_ids' => array_map('intval', $v['attribute_value_ids'] ?? []),
            'images' => [],
        ])
        : $oldVariations->map(function($v){
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
                <label>{{ __('app.select_attribute') }}</label>
                <select id="attributeSelect" class="form-control">
                    <option value="">{{ __('app.select') }}</option>
                    @foreach($attributes as $attribute)
                        <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" id="addAttributeBtn" class="btn btn-primary w-100">
                    {{ __('app.add_attribute') }}
                </button>
            </div>
        </div>
        <div id="attributesContainer" class="mt-3"></div>
        <button type="button" id="generateVariants" class="btn btn-primary mt-3">
            {{ __('app.generate_variants') }}
        </button>
    </div>

    <div class="alert alert-warning d-none" id="variantStaleWarning">{{ __('app.variants_attributes_changed') }}</div>

    <div class="table-responsive">
        <table class="table table-bordered d-none" id="variantTable">
            <thead><tr id="variantHeader"></tr></thead>
            <tbody id="variantBody"></tbody>
        </table>
    </div>
</div>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">{{ __('app.status') }}</div>
            <div class="card-body">
                <x-form.radio name="status"
                              :checked="$currentStatus"
                              :options="\App\Enums\ProductStatus::options()" />

                <hr>

                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                    <label class="custom-control-label" for="is_featured">{{ __('app.featured_product') }}</label>
                    <small class="text-muted d-block">{{ __('app.featured_product_help') }}</small>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">{{ __('app.product_organization') }}</div>
            <div class="card-body">
                <div class="form-group">
                    <x-form.select
                        name="category_id"
                        :label="__('app.category')"
                        :selected="old('category_id', $product->category_id ?? '')"
                        :options="$categories->pluck('name', 'id')"
                    />
                </div>
                <div class="form-group mb-0">
                    <x-form.select
                        name="brand_id"
                        :label="__('app.brand')"
                        :selected="old('brand_id', $product->brand_id ?? '')"
                        :options="$brands->pluck('name', 'id')"
                    />
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">{{ __('app.thumbnail') }}</div>
            <div class="card-body">
                <div id="thumbnailPreview" class="product-thumbnail-preview">
                    @if ($product && $product->thumbnail)
                        <img src="{{ asset('storage/' . $product->thumbnail->image) }}" alt="">
                    @else
                        <i class="lni lni-image"></i>
                    @endif
                </div>
                <input type="file" name="thumbnail" class="form-control mt-2" accept="image/jpeg,image/png,image/webp" onchange="previewThumbnail(event)">
                <small class="text-muted">{{ __('app.product_images_help') }}</small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4">{{ __($button_label ?? __('app.save')) }}</button>
    </div>
</div>

<script>
function previewThumbnail(event) {
    const file = event.target.files[0];
    if (!file) return;

    const img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    img.alt = '';
    document.getElementById('thumbnailPreview').replaceChildren(img);
}

// Shows the discount the compare price gives while the admin types.
(function () {
    const price = document.getElementById('product_price');
    const compare = document.getElementById('product_compare_price');
    const hint = document.getElementById('product_discount_hint');
    const text = @js(__('app.product_discount_hint'));
    const invalid = @js(__('app.compare_price_must_be_higher'));

    function update() {
        const p = parseFloat(price.value);
        const c = parseFloat(compare.value);

        if (!c || !p) {
            hint.textContent = '';
        } else if (c <= p) {
            hint.textContent = invalid;
        } else {
            hint.textContent = text.replace(':percent', Math.round((c - p) / c * 100));
        }
    }

    price.addEventListener('input', update);
    compare.addEventListener('input', update);
    update();
})();
</script>

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
    window.__PRODUCT_TEXT__ = @js([
        'chooseAttributes' => __('app.choose_attributes_first'),
        'attributesChanged' => __('app.variants_attributes_changed'),
        'price' => __('app.price'),
        'comparePrice' => __('app.compare_price'),
        'stock' => __('app.stock'),
        'images' => __('app.images'),
        'primary' => __('app.primary'),
        'delete' => __('app.delete'),
    ]);
    window.__ATTRIBUTES__ = @json($attributesForJs);
    window.__OLD_VARIATIONS__ = @json($variationsForJs);
   window.existingGallery = @json(isset($product) && $product->gallery ? $product->gallery->map(fn($img) => [
        'id' => $img->id,
        'path' => asset('storage/' . $img->image)
    ]) : []);
</script>

    @vite('resources/js/dashboard/product-page.js')

@endpush
