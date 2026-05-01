/**
 * Product Management System
 * Handling: Simple/Variable Switch, Attribute Management, Variant Generation, and Gallery
 */

document.addEventListener('DOMContentLoaded', function () {

    // --- 1. Elements ---
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

    // --- 2. State ---
    let selectedAttributes = [];
    let variants = window.__OLD_VARIATIONS__ || [];
    let galleryFiles = [];
    let existingGallery = window.existingGallery || [];
    let sortableGallery = null;

    // --- 3. Init ---
    if (variants.length > 0) {
        initExistingAttributes();
        renderAttributes();
        renderTable();
    }

    renderGallery();
    toggleSections();

    // --- 4. Events ---
    productType?.addEventListener('change', toggleSections);
    addAttributeBtn?.addEventListener('click', addNewAttribute);
    generateBtn?.addEventListener('click', generateVariants);

    // --- 5. Core ---

    function toggleSections() {
        const isVariable = productType.value === 'variable';

        simpleSection.style.display = isVariable ? 'none' : 'block';
        variableSection.style.display = isVariable ? 'block' : 'none';

        if (isVariable) {
            renderAttributes();
            renderTable();
        }
    }

    function initExistingAttributes() {
        variants.forEach(v => {
            v.attribute_value_ids.forEach(valId => {
                const attr = attributesLibrary.find(a =>
                    a.values.some(vv => vv.id == valId)
                );

                if (!attr) return;

                let exist = selectedAttributes.find(a => a.id == attr.id);

                if (!exist) {
                    selectedAttributes.push({ id: attr.id, values: [] });
                    exist = selectedAttributes.find(a => a.id == attr.id);
                }

                if (!exist.values.includes(valId)) {
                    exist.values.push(valId);
                }
            });
        });
    }

    function addNewAttribute() {
        const id = attributeSelect.value;
        if (!id || selectedAttributes.some(a => a.id == id)) return;

        selectedAttributes.push({ id, values: [] });

        attributeSelect.value = ''; // UX تحسين

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
        attributesContainer.innerHTML = '';

        selectedAttributes.forEach((attr, idx) => {
            const a = findAttribute(attr.id);
            if (!a) return;

            let html = `
                <div class="card mb-3 p-3 border shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>${a.name}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-attr" data-idx="${idx}">×</button>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
            `;

            a.values.forEach(v => {
                const checked = attr.values.includes(v.id) ? 'checked' : '';

                html += `
                    <label>
                        <input type="checkbox" class="attr-checkbox" data-idx="${idx}" value="${v.id}" ${checked}>
                        ${v.value}
                    </label>
                `;
            });

            html += `</div></div>`;

            attributesContainer.insertAdjacentHTML('beforeend', html);
        });

        document.querySelectorAll('.remove-attr').forEach(btn => {
            btn.onclick = e => {
                selectedAttributes.splice(e.target.dataset.idx, 1);
                renderAttributes();
            };
        });
    }

    attributesContainer.addEventListener('change', e => {
        if (!e.target.classList.contains('attr-checkbox')) return;

        const idx = e.target.dataset.idx;
        const val = parseInt(e.target.value);

        if (e.target.checked) {
            if (!selectedAttributes[idx].values.includes(val)) {
                selectedAttributes[idx].values.push(val);
            }
        } else {
            selectedAttributes[idx].values =
                selectedAttributes[idx].values.filter(v => v != val);
        }
    });

    // 🔥 التعديل المهم هنا
    function generateVariants() {

        const arrays = selectedAttributes.map(a => a.values);

        if (arrays.length === 0 || arrays.some(a => a.length === 0)) {
            alert('يرجى اختيار الخصائص والقيم أولاً');
            return;
        }

        const combos = arrays.reduce((a, b) =>
            a.flatMap(d => b.map(e => [...d, e])), [[]]
        );

        let newVariants = [];

        combos.forEach(combo => {

            const exists = variants.find(v =>
                JSON.stringify([...v.attribute_value_ids].sort()) ===
                JSON.stringify([...combo].sort())
            );

            if (!exists) {
                newVariants.push({
                    id: null,
                    attribute_value_ids: combo,
                    price: '',
                    compare_price: '',
                    stock: '',
                    sku: generateSku(combo),
                    images: []
                });
            }
        });

        // ✅ دمج القديم مع الجديد
        variants = [...variants, ...newVariants];

        renderTable();
    }

    function generateSku(values) {
        const nameEn = document.getElementById('product_name_en')?.value || '';
        const nameAr = document.getElementById('product_name_ar')?.value || '';

        const prefix = (nameEn || nameAr || 'PRD')
            .trim()
            .replace(/\s+/g, '-')
            .substring(0, 6)
            .toUpperCase();

        const attrs = values.map(v =>
            findValue(v).toString().substring(0, 3).toUpperCase()
        ).join('-');

        return `${prefix}-${attrs}-${Math.floor(1000 + Math.random() * 9000)}`;
    }

    function renderTable() {

        variantHeader.innerHTML = '';

        selectedAttributes.forEach(a => {
            variantHeader.insertAdjacentHTML('beforeend',
                `<th>${findAttribute(a.id).name}</th>`);
        });

        variantHeader.insertAdjacentHTML('beforeend', `
            <th>SKU</th>
            <th>السعر</th>
            <th>السعر السابق</th>
            <th>المخزون</th>
            <th>الصور</th>
            <th>أساسي</th>
            <th>حذف</th>
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

                <td><input name="variations[${index}][sku]" class="form-control" value="${v.sku}"></td>
                <td><input type="number" name="variations[${index}][price]" class="form-control" value="${v.price}"></td>
                <td><input type="number" name="variations[${index}][compare_price]" class="form-control" value="${v.compare_price}"></td>
                <td><input type="number" name="variations[${index}][stock]" class="form-control" value="${v.stock}"></td>

                <td>
                    <input type="file" name="variations[${index}][images][]" multiple class="form-control">
                </td>

                <td><input type="radio" name="primary" value="${index}"></td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm delete-variant" data-index="${index}">×</button>
                </td>
            </tr>`;

            variantBody.insertAdjacentHTML('beforeend', row);
        });

        variantTable.classList.toggle('d-none', variants.length === 0);
    }

    variantBody.addEventListener('click', e => {
        if (!e.target.classList.contains('delete-variant')) return;

        variants.splice(e.target.dataset.index, 1);
        renderTable();
    });

    // --- Gallery ---
    window.handleGalleryUpload = function (event) {
        const files = Array.from(event.target.files);
        galleryFiles.push(...files);
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
        div.className = 'img-box';

        div.innerHTML = `
            <img src="${src}">
            <button type="button">×</button>
        `;

        div.querySelector('button').onclick = () => {
            if (isExisting) {
                existingGallery = existingGallery.filter(i => i.id != id);
            } else {
                galleryFiles.splice(id, 1);
            }
            renderGallery();
        };

        return div;
    }

});