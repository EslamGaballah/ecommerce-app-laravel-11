{{-- ========================================= --}}
{{-- PRODUCT FORM --}}
{{-- ========================================= --}}

<div class="form-group">
    <x-form.input label="{{ __('app.name') }}"
                  class="form-control-lg"
                  name="name"
                  :value="old('name', $product->name ?? '')" />
</div>

<div class="form-group mb-3">
    <label> {{__('app.main_image')}} </label>
    <input type="file" name="main_image" class="form-control" accept="image/*" onchange="previewMainImage(event)">

    <div id="mainImagePreview" class="mt-2">
        @if(isset($product) && $product->main_image)
            <img src="{{ asset('storage/' . $product->main_image) }}" 
                 width="120" 
                 class="img-thumbnail">
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

{{-- CATEGORY --}}
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

{{-- BRAND --}}
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

{{-- STATUS --}}
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

{{-- PRODUCT TYPE --}}
<div class="form-group">
    <label>{{ __('app.product_type') }}</label>
    <select name="product_type"
            id="product_type"
            class="form-control form-select">
        <option value="simple"
            @selected(old('product_type', $product->product_type ?? 'simple') === 'simple')>
            Simple
        </option>
        <option value="variable"
            @selected(old('product_type', $product->product_type ?? '') === 'variable')>
            Variable
        </option>
    </select>
</div>

{{-- SIMPLE PRODUCT --}}
<div id="simple-section">
    <div class="row">
        <div class="col-md-4">
            <x-form.input type="number"
                          label="Price"
                          name="price"
                          :value="old('price', $product->price ?? '')" />
        </div>
        <div class="col-md-4">
            <x-form.input type="number"
                          label="Compare Price"
                          name="compare_price"
                          :value="old('compare_price', $product->compare_price ?? '')" />
        </div>
        <div class="col-md-4">
            <x-form.input type="number"
                          label="Stock"
                          name="stock"
                          :value="old('stock', $product->stock ?? '')" />
        </div>
    </div>

    <div class="form-group mt-3">
        <label>{{ __('app.images') }}</label>
        <input type="file"
               name="images[]"
               multiple
               class="form-control"
               accept="image/*"
               onchange="previewMultipleImages(event, 'simpleImagesPreview')">
    </div>
    <div id="simpleImagesPreview" class="row mt-3"></div>
</div>

{{-- VARIABLE PRODUCT --}}
<div id="variable-section" style="display:none">
    <hr>
    @php
        $attributesForJs = $attributes->map(fn($a)=>[
            'id'=>$a->id,
            'name'=>$a->name,
            'values'=>$a->attributeValues->map(fn($v)=>['id'=>$v->id,'value'=>$v->value])->toArray()
        ])->toArray();
    @endphp
    <script>window.__ATTRIBUTES__ = @json($attributesForJs);</script>

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
                <button type="button" id="addAttributeBtn" class="btn btn-secondary w-100">Add Attribute</button>
            </div>
            <div class="col-md-3">
                <button type="button" id="generateBtn" class="btn btn-dark w-100">Generate Variations</button>
            </div>
        </div>
        <div id="attributesContainer" class="mt-3"></div>
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

@php
$variationsForJs = $product->variations->map(function($v){
    return [
        'id' => $v->id,
        'price' => $v->price,
        'compare_price' => $v->compare_price,
        'stock' => $v->stock,
        'sku' => $v->sku,
        'is_primary' => $v->is_primary,
        'attribute_value_ids' => $v->values->pluck('id')->toArray(),

        // 🔥 إضافة صور الفارييشن
        'images' => $v->images->map(function($img){
            return [
                'id' => $img->id,
                'path' => asset('storage/'.$img->image)
            ];
        })->toArray()
    ];
});
@endphp



{{-- ========================================= --}}
{{-- SCRIPTS --}}
{{-- ========================================= --}}
@push('script')

<script>
window.__OLD_VARIATIONS__ = @json($variationsForJs);
</script>

<script>
document.addEventListener('DOMContentLoaded', function(){

    // Toggle Simple / Variable Sections
    const productType = document.getElementById('product_type');
    const simpleSection = document.getElementById('simple-section');
    const variableSection = document.getElementById('variable-section');
    function toggleSections(){
        if(productType.value==='variable'){
            simpleSection.style.display='none';
            variableSection.style.display='block';
        }else{
            simpleSection.style.display='block';
            variableSection.style.display='none';
        }
    }
    toggleSections();
    productType.addEventListener('change', toggleSections);

    // Multiple images preview for Simple Product
    window.previewMultipleImages = function(event, containerId){
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        [...event.target.files].forEach(file=>{
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-3';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.height='150px';
            img.style.objectFit='cover';
            img.className='img-fluid';
            col.appendChild(img);
            container.appendChild(col);
        });
    };

    // Variations Logic
    const attributesLibrary = window.__ATTRIBUTES__ || [];
    const attributeSelect = document.getElementById('attributeSelect');
    const addAttributeBtn = document.getElementById('addAttributeBtn');
    const generateBtn = document.getElementById('generateBtn');
    const attributesContainer = document.getElementById('attributesContainer');
    const variantTable = document.getElementById('variantTable');
    const variantHeader = document.getElementById('variantHeader');
    const variantBody = document.getElementById('variantBody');

    let selectedAttributes = [];
    let variants = window.__OLD_VARIATIONS__ || [];

    if(variants.length){

    variants.forEach(v=>{

        v.attribute_value_ids.forEach(valId=>{

            const attr = attributesLibrary.find(a =>
                a.values.some(v=>v.id == valId)
            );

            if(attr){

                let exist = selectedAttributes.find(a => a.id == attr.id);

                if(!exist){
                    selectedAttributes.push({
                        id: attr.id,
                        values: []
                    });
                    exist = selectedAttributes.find(a => a.id == attr.id);
                }

                if(!exist.values.includes(valId)){
                    exist.values.push(valId);
                }

            }

        });

    });

    renderAttributes();
    renderTable();
}

    function getCombos(arrays){ return arrays.reduce((a,b)=>a.flatMap(d=>b.map(e=>[...d,e])),[[]]); }
    function findAttribute(id){ return attributesLibrary.find(a=>a.id==id); }
    function findValue(valueId){ for(let a of attributesLibrary){ const v=a.values.find(v=>v.id==valueId); if(v) return v.value;} return valueId;}

    function renderAttributes(){
        attributesContainer.innerHTML='';
        selectedAttributes.forEach((attr,idx)=>{
            const a = findAttribute(attr.id);
            let html=`<div class="mb-2"><strong>${a.name}</strong><br>`;
            a.values.forEach(v=>{
                html+=`<label class="me-2"><input type="checkbox" data-attr="${idx}" value="${v.id}"> ${v.value}</label>`;
            });
            html+='</div>';
            attributesContainer.innerHTML+=html;
        });
    }

    addAttributeBtn.addEventListener('click', ()=>{
        const id = attributeSelect.value; if(!id) return;
        if(selectedAttributes.some(a=>a.id==id)) return;
        selectedAttributes.push({id,values:[]});
        renderAttributes();
    });

    attributesContainer.addEventListener('change', e=>{
        if(e.target.type==='checkbox'){
            const idx = e.target.dataset.attr;
            const val = e.target.value;
            if(e.target.checked){
                if(!selectedAttributes[idx].values.includes(val)) selectedAttributes[idx].values.push(val);
            }else{
                selectedAttributes[idx].values = selectedAttributes[idx].values.filter(v=>v!=val);
            }
        }
    });

    generateBtn.addEventListener('click', ()=>{
        if(selectedAttributes.length===0){ alert("Choose attribute first"); return; }
        if(selectedAttributes.some(a=>a.values.length===0)){ alert("Choose values first"); return; }
        const combos = getCombos(selectedAttributes.map(a=>a.values));
        variants = combos.map((combo,i)=>({
            attribute_value_ids: combo,
            sku: combo.map(v=>v).join('-'), // Auto SKU simple version
            price:'',
            compare_price:'',
            stock:'',
            image:null
        }));
        renderTable();
    });

    // 🔥 render table
function renderTable(){

    variantHeader.innerHTML='';

    // عرض اسم الاتريبيوت
    selectedAttributes.forEach(a=>{
        variantHeader.innerHTML+=
            `<th>${findAttribute(a.id).name}</th>`;
    });

    variantHeader.innerHTML+=`
        <th>SKU</th>
        <th>Price</th>
        <th>Compare</th>
        <th>Stock</th>
        <th>Image</th>
        <th>Primary</th>
        <th>Delete</th>
    `;

    variantBody.innerHTML='';

    variants.forEach((v,index)=>{

        let row = `<tr>`;

        // 🔥 عرض القيمة بدل الـ ID + إنشاء sku تلقائي
        let skuParts = [];

        v.attribute_value_ids.forEach(val=>{
            const valueText = findValue(val); // نحصل على القيمة النصية
            skuParts.push(valueText);          // نضيفها للـ SKU
            row+=`
                <td>${valueText}</td>
                <input type="hidden"
                       name="variations[${index}][attribute_value_ids][]"
                       value="${val}">
            `;
        });

        // 🔥 إنشاء SKU تلقائي من القيم مفصولة بـ "-"
        const skuValue = skuParts.join('-');

        row+=`

        <input type="hidden"
                name="variations[${index}][id]"
                value="${v.id ?? ''}">
                
            <td>
                <input name="variations[${index}][sku]"
                       class="form-control"
                       value="${v.sku ?? skuValue}">
            </td>

           <td>
                <input name="variations[${index}][price]"
                    type="number"
                    class="form-control"
                    value="${v.price ?? ''}">
            </td>

            <td>
                <input name="variations[${index}][compare_price]"
                    type="number"
                    class="form-control"
                    value="${v.compare_price ?? ''}">
            </td>

            <td>
                <input name="variations[${index}][stock]"
                    type="number"
                    class="form-control"
                    value="${v.stock ?? ''}">
            </td>

            <td>

                <input type="file"
                    name="variations[${index}][images][]"
                    class="form-control"
                    multiple
                    accept="image/*">

                <div class="d-flex flex-wrap mt-2">

                    ${(v.images ?? []).map(img => `
                        <div style="position:relative;margin-right:5px">
                            <img src="${img.path}"
                                style="width:60px;height:60px;object-fit:cover"
                                class="border">

                            <button type="button"
                                    class="btn btn-sm btn-danger delete-image"
                                    data-id="${img.id}"
                                    style="position:absolute;top:-5px;right:-5px">
                                    x
                            </button>
                        </div>
                    `).join('')}

                </div>

            </td>

            <td class="text-center">
                <input type="radio"
                       name="primary"
                       value="${index}"
                       ${v.is_primary ? 'checked' : ''}>
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-danger delete-variant"
                        data-index="${index}">
                    X
                </button>
            </td>
        </tr>`;

        variantBody.innerHTML+=row;
    });

    variantTable.classList.remove('d-none');
}

    // Delete variant row
    variantBody.addEventListener('click', function(e){
        if(e.target.classList.contains('delete-variant')){
            const index = e.target.dataset.index;
            variants.splice(index,1);
            renderTable();
        }
    });

    // Preview for variation images
    window.previewVariationImage = function(event,index){
        const container = document.getElementById('variationPreview'+index);
        container.innerHTML='';
        const file = event.target.files[0];
        if(file){
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.height='100px';
            img.style.objectFit='cover';
            img.className='img-fluid';
            container.appendChild(img);
        }
    }

});
</script>
@endpush