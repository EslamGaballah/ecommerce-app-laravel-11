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
    const staleWarning = document.getElementById('variantStaleWarning');

    // --- 2. State ---
    let selectedAttributes = [];
    let variants = (window.__OLD_VARIATIONS__ || []).map(variant => ({
        ...variant,
        attribute_value_ids: variant.attribute_value_ids.map(Number),
    }));
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

    // Saving rows that don't match the chosen attributes would store incomplete variations.
    variantTable?.closest('form')?.addEventListener('submit', e => {
        if (productType.value === 'variable' && variantsAreStale()) {
            e.preventDefault();
            alert(window.__PRODUCT_TEXT__.attributesChanged);
        }
    });

    // --- 5. Core ---

    function toggleSections() {
        const isVariable = productType.value === 'variable';

        simpleSection.style.display = isVariable ? 'none' : 'block';
        variableSection.style.display = isVariable ? 'block' : 'none';

        if (isVariable) {
            syncFromTable();
            renderAttributes();
            renderTable();
        }
    }

    function initExistingAttributes() {
        variants.forEach(v => {
            v.attribute_value_ids.forEach(valId => {
                const attr = attributeOfValue(valId);

                if (!attr) return;

                let exist = selectedAttributes.find(a => a.id == attr.id);

                if (!exist) {
                    exist = { id: attr.id, values: [] };
                    selectedAttributes.push(exist);
                }

                if (!exist.values.includes(valId)) {
                    exist.values.push(valId);
                }
            });
        });
    }

    function addNewAttribute() {
        const id = Number(attributeSelect.value);
        if (!id || selectedAttributes.some(a => a.id == id)) return;

        syncFromTable();

        selectedAttributes.push({ id, values: [] });

        attributeSelect.value = ''; // UX تحسين

        renderAttributes();
        renderTable();
    }

    function findAttribute(id) {
        return attributesLibrary.find(a => a.id == id);
    }

    function attributeOfValue(valueId) {
        return attributesLibrary.find(a => a.values.some(v => v.id == valueId));
    }

    function findValue(valueId) {
        for (let a of attributesLibrary) {
            const v = a.values.find(v => v.id == valueId);
            if (v) return v.value;
        }
        return valueId;
    }

    function escapeHtml(text) {
        const box = document.createElement('div');
        box.textContent = text ?? '';

        return box.innerHTML.replace(/"/g, '&quot;');
    }

    function renderAttributes() {
        attributesContainer.innerHTML = '';

        selectedAttributes.forEach((attr, idx) => {
            const a = findAttribute(attr.id);
            if (!a) return;

            let html = `
                <div class="card mb-3 p-3 border shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>${escapeHtml(a.name)}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-attr" data-idx="${idx}">×</button>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
            `;

            a.values.forEach(v => {
                const checked = attr.values.includes(v.id) ? 'checked' : '';

                html += `
                    <label>
                        <input type="checkbox" class="attr-checkbox" data-idx="${idx}" value="${v.id}" ${checked}>
                        ${escapeHtml(v.value)}
                    </label>
                `;
            });

            html += `</div></div>`;

            attributesContainer.insertAdjacentHTML('beforeend', html);
        });

        document.querySelectorAll('.remove-attr').forEach(btn => {
            btn.onclick = e => {
                syncFromTable();
                selectedAttributes.splice(e.target.dataset.idx, 1);
                renderAttributes();
                renderTable();
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

    function sameValues(a, b) {
        return a.length === b.length && a.every(value => b.includes(value));
    }

    function generateVariants() {

        const arrays = selectedAttributes.map(a => a.values);

        if (arrays.length === 0 || arrays.some(a => a.length === 0)) {
            alert(window.__PRODUCT_TEXT__.chooseAttributes);
            return;
        }

        syncFromTable();

        const combos = arrays.reduce((a, b) =>
            a.flatMap(d => b.map(e => [...d, e])), [[]]
        );

        // A combination that already exists keeps its row; those rows can't also become a parent.
        const taken = new Set(
            variants.filter(v => combos.some(combo => sameValues(v.attribute_value_ids, combo)))
        );

        // The table always matches the chosen attributes, so rows for anything else are replaced.
        variants = combos.map(combo => {
            const exact = variants.find(v => sameValues(v.attribute_value_ids, combo));

            if (exact) return { ...exact, attribute_value_ids: combo };

            const parent = findParent(combo);
            const isHeir = parent && !taken.has(parent);

            if (isHeir) taken.add(parent);

            return {
                // One row carries on the saved variation, with its stock and photos.
                id: isHeir ? parent.id : null,
                sku: isHeir ? parent.sku : generateSku(combo),
                stock: isHeir ? parent.stock : '',
                images: isHeir ? parent.images : [],
                files: isHeir ? parent.files : null,
                is_primary: isHeir ? parent.is_primary : false,
                // Every row built from it starts at the same price, which saves retyping it.
                price: parent ? parent.price : '',
                compare_price: parent ? parent.compare_price : '',
                attribute_value_ids: combo,
            };
        });

        renderTable();
    }

    /**
     * The earlier row this combination grew from: an attribute was added (the row's values are
     * all in the combination) or removed (the combination's values are all in the row).
     */
    function findParent(combo) {
        return variants.find(v =>
            v.attribute_value_ids.every(value => combo.includes(value)) ||
            combo.every(value => v.attribute_value_ids.includes(value))
        );
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

    /** Whether any row was made for a different set of attributes than the chosen one. */
    function variantsAreStale() {
        const chosen = selectedAttributes.map(a => Number(a.id));

        return variants.some(v => {
            const used = v.attribute_value_ids.map(value => Number(attributeOfValue(value)?.id));

            return !sameValues([...new Set(used)], chosen);
        });
    }

    /**
     * What the admin typed lives in the inputs, and drawing the table replaces them,
     * so it is copied back first. File inputs can't be refilled by value, so their list is kept.
     */
    function syncFromTable() {
        variantBody.querySelectorAll('tr[data-index]').forEach(row => {
            const variant = variants[row.dataset.index];

            if (!variant) return;

            variant.sku = row.querySelector('[data-field="sku"]').value;
            variant.price = row.querySelector('[data-field="price"]').value;
            variant.compare_price = row.querySelector('[data-field="compare_price"]').value;
            variant.stock = row.querySelector('[data-field="stock"]').value;
            variant.files = row.querySelector('[data-field="images"]').files;
            variant.is_primary = row.querySelector('[data-field="primary"]').checked;
        });
    }

    function renderTable() {

        variantHeader.innerHTML = '';

        selectedAttributes.forEach(a => {
            variantHeader.insertAdjacentHTML('beforeend',
                `<th>${escapeHtml(findAttribute(a.id).name)}</th>`);
        });

        variantHeader.insertAdjacentHTML('beforeend', `
            <th>SKU</th>
            <th>${window.__PRODUCT_TEXT__.price}</th>
            <th>${window.__PRODUCT_TEXT__.comparePrice}</th>
            <th>${window.__PRODUCT_TEXT__.stock}</th>
            <th>${window.__PRODUCT_TEXT__.images}</th>
            <th>${window.__PRODUCT_TEXT__.primary}</th>
            <th>${window.__PRODUCT_TEXT__.delete}</th>
        `);

        variantBody.innerHTML = '';

        // Exactly one row is the primary; the store shows it first.
        const primaryIndex = Math.max(variants.findIndex(v => v.is_primary), 0);

        variants.forEach((v, index) => {

            let row = `<tr data-index="${index}">`;

            // One cell per column, in the header's order, whichever order the values were saved in.
            selectedAttributes.forEach(attr => {
                const library = findAttribute(attr.id);
                const value = v.attribute_value_ids.find(id => library.values.some(item => item.id == id));

                row += `<td>${value === undefined ? '—' : escapeHtml(findValue(value))}</td>`;
            });

            const hiddenValues = v.attribute_value_ids
                .map(val => `<input type="hidden" name="variations[${index}][attribute_value_ids][]" value="${val}">`)
                .join('');

            row += `
                <td>
                    ${hiddenValues}
                    <input type="hidden" name="variations[${index}][id]" value="${v.id ?? ''}">
                    <input name="variations[${index}][sku]" data-field="sku" class="form-control" value="${escapeHtml(v.sku)}">
                </td>
                <td><input type="number" step="0.01" min="0" name="variations[${index}][price]" data-field="price" class="form-control" value="${escapeHtml(v.price)}"></td>
                <td><input type="number" step="0.01" min="0" name="variations[${index}][compare_price]" data-field="compare_price" class="form-control" value="${escapeHtml(v.compare_price)}"></td>
                <td><input type="number" step="1" min="0" name="variations[${index}][stock]" data-field="stock" class="form-control" value="${escapeHtml(v.stock)}"></td>

                <td>
                    <input type="file" name="variations[${index}][images][]" data-field="images" multiple accept="image/jpeg,image/png,image/webp" class="form-control">
                </td>

                <td><input type="radio" name="primary" data-field="primary" value="${index}" ${index === primaryIndex ? 'checked' : ''}></td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm delete-variant" data-index="${index}">×</button>
                </td>
            </tr>`;

            variantBody.insertAdjacentHTML('beforeend', row);

            if (v.files?.length) {
                variantBody.lastElementChild.querySelector('[data-field="images"]').files = v.files;
            }
        });

        variantTable.classList.toggle('d-none', variants.length === 0);
        staleWarning?.classList.toggle('d-none', !variantsAreStale());
    }

    variantBody.addEventListener('click', e => {
        if (!e.target.classList.contains('delete-variant')) return;

        syncFromTable();
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
