<div class="form-group">
    {{-- <label for=""> {{ __ ('app.products')}} </label> --}}
    <x-form.input label="{{ __ ('app.name')}}" class="form-control-lg" role="input" name="name" :value="$product->name" />
</div>

<div class="form-group">
    <label for="" required >{{ __ ('app.description')}}</label>
    <x-form.textarea name="description" :value="$product->description" />
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
    <label for="">{{ __ ('app.brand')}}</label>
    <select name="brand_id" class="form-control form-select">
        <option value="">{{ __ ('app.brand')}}</option>
        @foreach(App\Models\Brand::all() as $brand)
        <option value="{{ $brand->id }}"
             @selected(old('brand_id', $product->brand_id) == $brand->id)>
             {{ $brand->name }}
        </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    @php
        $currentStatus = old(
            'status',
            $product->status?->value ?? \App\Enums\ProductStatus::Draft->value
        );
    @endphp
    <label for="">{{ __ ('app.status')}}</label>
    <div>
        <x-form.radio
            name="status"
            :checked="$currentStatus"
            :options="\App\Enums\ProductStatus::options()"
        />
        @if(isset($product) && $product->status)
            <span class="badge bg-{{ $product->status->color() }} mt-2">
                {{ $product->status->label() }}
            </span>
        @endif
    </div>
</div>
{{-- product images --}}
<div class="form-group">
    <label class="form-label">{{ __('app.images') }}</label>
    <input 
        type="file"
        name="image[]"
        id="product-images"
        class="form-control"
        multiple
        accept="image/*"
    />
    <div id="product-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
</div>

<label>{{ __('app.product_type') }} </label>
    <select name="product_type" id="product_type" class="form-control form-select">
        <option value="simple"
            @selected(old('product_type', $product->product_type ?? 'simple') === 'simple')>
            Simple
        </option>

        <option value="variable"
            @selected(old('product_type', $product->product_type ?? '') === 'variable')>
            Variable
        </option>
    </select>


{{-- simple product --}}
<div id="single-fields">
    <x-form.input label="Price" name="price"/>
    <x-form.input label="compare Price" name="compare_price"/>
    <x-form.input label="Stock" name="stock"/>
</div>

{{-- start variations --}}
<div id="variations-wrapper" style="display:none">

@php
    $oldVariations = old(
        'variations',
        $product->exists
            ? $product->variations
            : [[]]
    );
@endphp

    @foreach($oldVariations as $index => $vData)
    <div class="row align-items-end variation-row border rounded p-3 mb-3">

         {{-- حقول الـ Attributes --}}
        <div class="form-group col-md-4">
            <div class="row g-2">
                @foreach($attributes as $attrIndex => $attribute)
                    <div class="col">
                        <select name="variations[{{ $index }}][attributes][]" class="form-control form-select" required>
                            <option value="">{{ $attribute->name }}...</option>
                            @foreach($attribute->attributeValues as $value)
                                <option value="{{ $value->id }}" 
                                    @selected(
                                        collect(old("variations.$index.attributes", isset($vData['values']) ? $vData['values']->pluck('id') : []))
                                        ->contains($value->id)
                                    )>
                                    {{ $value->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group col-md-2">
            <x-form.input label="{{ __('app.sku') }}" 
                name="variations[{{ $index }}][sku]" 
                :value="old('variations.'.$index.'.sku', $vData['sku'] ?? '')" />
        </div>

        <div class="form-group col-md-2">
            <x-form.input type="number" label="{{ __('app.stock') }}" 
                name="variations[{{ $index }}][stock]" 
                :value="old('variations.'.$index.'.stock', $vData['stock'] ?? '')" />
        </div>

        <div class="form-group col-md-2">
            <x-form.input type="number" label="{{ __('app.price') }}" 
                name="variations[{{ $index }}][price]" 
                :value="old('variations.'.$index.'.price', $vData['price'] ?? '')" />
        </div>

        <div class="form-group col-md-2">
            <x-form.input type="number" label="{{ __('app.compare_price') }}" 
                name="variations[{{ $index }}][compare_price]" 
                :value="old('variations.'.$index.'.compare_price', $vData['compare_price'] ?? '')" />
        </div>

         {{-- حقل الـ Primary --}}
        <div class="col-md-2 text-center">
            <label class="form-label d-block">Primary</label>
            <input type="radio" name="primary" value="{{ $index }}" 
                @checked(old('primary', $product->primary_variation_id ?? 0) == $index)>
        </div>

         {{-- صور الـ Variation --}}
        <div class="col-md-2">
            <label class="form-label">{{ __('app.images') }}</label>

            <input 
                type="file"
                name="variations[{{ $index }}][images][]"
                class="form-control variation-images"
                multiple
                accept="image/*"
                onchange="previewImages(this, 'preview-{{ $index }}')" {{-- استدعاء الدالة --}}
            />

            {{-- مكان عرض الصور الجديدة (المعاينة) --}}
            <div id="preview-{{ $index }}" class="preview-images d-flex flex-wrap gap-2 mt-2"></div>

            {{-- عرض الصور القديمة في حالة التعديل --}}
            @if(isset($vData['images']) && count($vData['images']))
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($vData['images'] as $image)
                        <div class="position-relative">
                            <img src="{{ asset('storage/'.$image->path) }}"
                                width="50"
                                height="50"
                                class="rounded border">

                            {{-- لاحقًا نربطه بحذف صورة --}}
                            <input type="hidden" 
                                name="variations[{{ $index }}][existing_images][]" 
                                value="{{ $image->id }}">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-md-1">
        <button type="button" class="btn btn-danger btn-sm remove-variation">
            <i class="fa fa-trash"></i>
        </button>
        </div>

    </div>
    @endforeach

    <button type="button" id="add-variation" class="btn btn-secondary mb-3">Add Another Variation</button>

</div>
<div class="form-group">
    <button type="submit" class="btn btn-primary">{{ __($button_label ?? 'Save') }}</button>
</div>

@push('script')

<script>
    //  preview product images
    document.getElementById('product-images')?.addEventListener('change', function(e){

    const preview = document.getElementById('product-preview');
    preview.innerHTML = '';

    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = ev => {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.width = 70;
            img.height = 70;
            img.className = 'rounded border shadow-sm me-2 mb-2';
            preview.appendChild(img);
        }
        reader.readAsDataURL(file);
    });

});

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const productTypeSelect = document.getElementById('product_type');
    const singleFields = document.getElementById('single-fields');
    const variationsWrapper = document.getElementById('variations-wrapper');

    function toggleFields() {

        const isVariable = productTypeSelect.value === 'variable';

        singleFields.style.display = isVariable ? 'none' : 'block';
        variationsWrapper.style.display = isVariable ? 'block' : 'none';

        // تفعيل required للـ attributes فقط عند variable
        document.querySelectorAll('#variations-wrapper select').forEach(select => {
            select.required = isVariable;
        });
    }

    toggleFields();
    productTypeSelect.addEventListener('change', toggleFields);

});

</script>

<script>

document.addEventListener('DOMContentLoaded', function(){

    const wrapper = document.getElementById('variations-wrapper');
    let index = wrapper.querySelectorAll('.variation-row').length;

     // المعاينة لكل input file باستخدام delegation
    wrapper.addEventListener('change', function(e){
        if(e.target.matches('input[type="file"].variation-images')) {
            const row = e.target.closest('.variation-row');
            const previewDiv = row.querySelector('.preview-images');
            previewDiv.innerHTML = '';

            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = ev => {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.width = 50;
                    img.height = 50;
                    img.className = 'rounded border shadow-sm me-2 mb-2';
                    previewDiv.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }
    });

    // حذف أي variation
    wrapper.addEventListener('click', function(e){
        if(e.target.closest('.remove-variation')){
            const row = e.target.closest('.variation-row');
            if(wrapper.querySelectorAll('.variation-row').length > 1){
                row.remove();
            } else {
                alert('يجب وجود متغير واحد على الأقل');
            }
        }
    });

    // إضافة variation جديد
    document.getElementById('add-variation').addEventListener('click', function(){
        const firstRow = wrapper.querySelector('.variation-row');
        const clone = firstRow.cloneNode(true);

        // مسح القيم القديمة
        clone.querySelectorAll('input[type="text"], input[type="number"]').forEach(i => i.value = '');
        clone.querySelectorAll('input[type="file"]').forEach(i => i.value = null);
        clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        clone.querySelectorAll('.preview-images').forEach(d => d.innerHTML = '');
        clone.querySelectorAll('input[type="radio"]').forEach(r => { r.checked = false; r.value = index; });

        // تحديث names
        clone.querySelectorAll('input, select').forEach(el => {
            if(el.name) el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
        });

        wrapper.appendChild(clone);
        index++;
    });

});
</script>
@endpush
