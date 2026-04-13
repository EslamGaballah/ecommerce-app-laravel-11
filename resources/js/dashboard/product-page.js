/**
 * Product Management System
 * Handling: Simple/Variable Switch, Attribute Management, Variant Generation, and Gallery
 */

document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Elements Selection ---
    const productType = document.getElementById('product_type');
    const simpleSection = document.getElementById('simple-section');
    const variableSection = document.getElementById('variable-section');

    const attributesLibrary = window.__ATTRIBUTES__ || [];
    const attributeSelect = document.getElementById('attributeSelect');
    const addAttributeBtn = document.getElementById('addAttributeBtn');
    const attributesContainer = document.getElementById('attributesContainer');

    const variantTable = document.getElementById('variantTable');
    const variantHeader = document.getElementById('variantHeader');
    const variantBody = document.getElementById('variantBody');
    const generateBtn = document.getElementById('generateVariants');

    // --- 2. State Management ---
    let selectedAttributes = [];
    let variants = window.__OLD_VARIATIONS__ || [];
    let galleryFiles = [];
    let existingGallery = window.existingGallery || [];
    let sortableGallery = null;

    // --- 3. Initial Execution ---
    // initExistingAttributes();
    // renderAttributes();
    // renderTable();
    // renderGallery();
    // toggleSections();

    if (window.__OLD_VARIATIONS__ && window.__OLD_VARIATIONS__.length > 0) {
    variants = window.__OLD_VARIATIONS__;
    initExistingAttributes();
    renderAttributes();
    renderTable();
}

renderGallery();
toggleSections();

    // --- 4. Event Listeners ---
    if (productType) {
        productType.addEventListener('change', toggleSections);
    }

    if (addAttributeBtn) {
        addAttributeBtn.addEventListener('click', addNewAttribute);
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', generateVariants);
    }

    // --- 5. Core Functions ---

    function toggleSections() {
        if (!productType) return;
        const isVariable = productType.value === 'variable';
        if (simpleSection) simpleSection.style.display = isVariable ? 'none' : 'block';
        if (variableSection) variableSection.style.display = isVariable ? 'block' : 'none';

        if (isVariable) {
        renderAttributes();
        renderTable();
    }
    }

    function initExistingAttributes() {
        if (variants.length) {
            variants.forEach(v => {
                v.attribute_value_ids.forEach(valId => {
                    const attr = attributesLibrary.find(a => a.values.some(vv => vv.id == valId));
                    if (attr) {
                        let exist = selectedAttributes.find(a => a.id == attr.id);
                        if (!exist) {
                            selectedAttributes.push({ id: attr.id, values: [] });
                            exist = selectedAttributes.find(a => a.id == attr.id);
                        }
                        if (!exist.values.includes(valId)) {
                            exist.values.push(valId);
                        }
                    }
                });
            });
        }
    }

    function addNewAttribute() {
        const id = attributeSelect.value;
        if (!id || selectedAttributes.some(a => a.id == id)) return;
        selectedAttributes.push({ id, values: [] });
        renderAttributes();
    }

    function findAttribute(id) {
        return attributesLibrary.find(a => a.id == id);
    }

    function findValue(valueId) {
        for (let a of attributesLibrary) {
            const v = a.values.find(v => v.id == valueId);
            if (v) return v.value;
        }
        return valueId;
    }

    function renderAttributes() {
        if (!attributesContainer) return;
        attributesContainer.innerHTML = '';

        selectedAttributes.forEach((attr, idx) => {
            const a = findAttribute(attr.id);
            if (!a) return;

            let html = `<div class="card mb-3 p-3 border shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="m-0"><strong>${a.name}</strong></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-attr-group" data-idx="${idx}">×</button>
                </div>
                <div class="d-flex flex-wrap gap-3">`;

            a.values.forEach(v => {
                const checked = attr.values.includes(v.id) ? 'checked' : '';
                html += `
                    <label class="d-flex align-items-center cursor-pointer">
                        <input type="checkbox" class="attr-checkbox me-1" data-attr="${idx}" value="${v.id}" ${checked}>
                        <span>${v.value}</span>
                    </label>`;
            });

            html += `</div></div>`;
            attributesContainer.insertAdjacentHTML('beforeend', html);
        });

        // Attach delete event for attribute groups
        document.querySelectorAll('.remove-attr-group').forEach(btn => {
            btn.onclick = (e) => {
                selectedAttributes.splice(e.target.dataset.idx, 1);
                renderAttributes();
            };
        });
    }

    // Handle Checkbox Changes
    attributesContainer.addEventListener('change', e => {
        if (e.target.classList.contains('attr-checkbox')) {
            const idx = e.target.dataset.attr;
            const val = parseInt(e.target.value);
            if (e.target.checked) {
                if (!selectedAttributes[idx].values.includes(val))
                    selectedAttributes[idx].values.push(val);
            } else {
                selectedAttributes[idx].values = selectedAttributes[idx].values.filter(v => v != val);
            }
        }
    });

    function generateVariants() {
        const arrays = selectedAttributes.map(a => a.values);
        if (arrays.length === 0 || arrays.some(a => a.length === 0)) {
            alert('يرجى اختيار الخصائص والقيم أولاً');
            return;
        }

        const combos = arrays.reduce((a, b) => a.flatMap(d => b.map(e => [...d, e])), [[]]);
        variants = combos.map(c => ({
            id: null,
            attribute_value_ids: c,
            price: '',
            compare_price: '',
            stock: '',
            sku: generateSku(c),
            images: []
        }));

        renderTable();
    }

    function generateSku(values) {
        const nameEn = document.getElementById('product_name_en')?.value || '';
        const nameAr = document.getElementById('product_name_ar')?.value || '';
        const prefix = (nameEn || nameAr || 'PRD').trim().replace(/\s+/g, '-').substring(0, 6).toUpperCase();
        
        const attrs = values.map(v => {
            const val = findValue(v);
            return val.toString().substring(0, 3).toUpperCase();
        }).join('-');

        return `${prefix}-${attrs}-${Math.floor(1000 + Math.random() * 9000)}`;
    }

    function renderTable() {
        if (!variantHeader || !variantBody) return;
        variantHeader.innerHTML = '';
        

        selectedAttributes.forEach(a => {
            variantHeader.insertAdjacentHTML('beforeend', `<th>${findAttribute(a.id).name}</th>`);
        });

        variantHeader.insertAdjacentHTML('beforeend', `
            <th>SKU</th><th>السعر</th><th>السعر السابق</th><th>المخزون</th><th>الصور</th><th>أساسي</th><th>حذف</th>
        `);

        variantBody.innerHTML = '';
        variants.forEach((v, index) => {
            let row = `<tr>`;
            v.attribute_value_ids.forEach(val => {
                row += `<td>${findValue(val)}</td>
                        <input type="hidden" name="variations[${index}][attribute_value_ids][]" value="${val}">`;
            });

            row += `
                <input type="hidden" name="variations[${index}][id]" value="${v.id ?? ''}">
                <td><input name="variations[${index}][sku]" class="form-control" value="${v.sku ?? generateSku(v.attribute_value_ids)}"></td>
                <td><input type="number" name="variations[${index}][price]" class="form-control" value="${v.price ?? ''}"></td>
                <td><input type="number" name="variations[${index}][compare_price]" class="form-control" value="${v.compare_price ?? ''}"></td>
                <td><input type="number" name="variations[${index}][stock]" class="form-control" value="${v.stock ?? ''}"></td>
                <td>
                    <input type="file" class="form-control variation-image-input" data-index="${index}" multiple accept="image/*" name="variations[${index}][images][]">
                    <div class="image-preview d-flex flex-wrap mt-2" id="preview-${index}">
                        ${(v.images ?? []).map(img => `
                            <div class="img-box position-relative me-1 mb-1">
                                <img src="${img.path}" style="width:50px; height:50px; object-fit:cover;">
                                <button type="button" class="btn btn-danger btn-sm delete-old-image position-absolute top-0 end-0" data-id="${img.id}">×</button>
                            </div>
                        `).join('')}
                    </div>
                </td>
                <td class="text-center"><input type="radio" name="primary" value="${index}"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm delete-variant" data-index="${index}">×</button></td>
            </tr>`;
            variantBody.insertAdjacentHTML('beforeend', row);
        });

        // variantTable.classList.toggle('d-none', variants.length === 0);
        if (variants.length > 0) {
        variantTable.classList.remove('d-none');
    } else {
        variantTable.classList.add('d-none');
    }
        enableSortable();
    }

    // --- 6. Gallery System ---
    window.handleGalleryUpload = function (event) {
        const files = Array.from(event.target.files);
        files.forEach(file => galleryFiles.push(file));
        renderGallery();
    };

    
    function renderGallery() {
    const container = document.getElementById('galleryPreview');
    if (!container) return;

    container.innerHTML = '';

    existingGallery.forEach(img =>
        container.appendChild(createGalleryBox(img.path, img.id, true))
    );

    galleryFiles.forEach((file, index) =>
        container.appendChild(createGalleryBox(URL.createObjectURL(file), index, false))
    );

    if (!sortableGallery && typeof Sortable !== 'undefined') {
        sortableGallery = new Sortable(container, { animation: 150 });
    }
}

    function createGalleryBox(src, id, isExisting) {
        const div = document.createElement('div');
        div.className = 'img-box m-1 border position-relative';
        div.style.width = '100px';
        div.innerHTML = `<img src="${src}" class="w-100 h-100 object-fit-cover"><button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0">×</button>`;
        div.querySelector('button').onclick = () => {
            if (isExisting) {
                markGalleryDeleted(id);
                existingGallery = existingGallery.filter(i => i.id != id);
            } else {
                galleryFiles.splice(id, 1);
            }
            renderGallery();
        };
        return div;
    }

    function markGalleryDeleted(id) {
        let input = document.querySelector('input[name="deleted_gallery"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_gallery';
            document.querySelector('form').appendChild(input);
        }
        let ids = input.value ? input.value.split(',') : [];
        ids.push(id);
        input.value = ids.join(',');
    }

    // --- 7. Variation Event Delegation ---
    variantBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('variation-image-input')) {
            const index = e.target.dataset.index;
            const container = document.getElementById('preview-' + index);
            [...e.target.files].forEach(file => {
                const box = document.createElement('div');
                box.className = 'img-box position-relative me-1 mb-1';
                box.innerHTML = `<img src="${URL.createObjectURL(file)}" style="width:50px; height:50px; object-fit:cover;">
                                 <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0">×</button>`;
                box.querySelector('button').onclick = () => box.remove();
                container.appendChild(box);
            });
        }
    });

    variantBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-variant')) {
            variants.splice(e.target.dataset.index, 1);
            renderTable();
        }
        if (e.target.classList.contains('delete-old-image')) {
            const id = e.target.dataset.id;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_images[]';
            input.value = id;
            document.querySelector('form').appendChild(input);
            e.target.closest('.img-box').remove();
        }
    });

    function enableSortable() {
        if (typeof Sortable === 'undefined') return;
        document.querySelectorAll('.image-preview').forEach(c => new Sortable(c, { animation: 150 }));
    }
});