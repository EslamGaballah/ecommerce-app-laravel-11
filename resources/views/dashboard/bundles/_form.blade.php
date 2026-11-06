@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $selected = collect(old('products', $bundle->exists ? $bundle->products->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
    $tiers = old('tiers', $bundle->exists
        ? $bundle->tiers->map(fn ($tier) => $tier->only(['min_quantity', 'discount_type', 'discount_value']))->all()
        : [['min_quantity' => 3, 'discount_type' => 'percent', 'discount_value' => 10]]);
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">{{ __('app.product_basic_info') }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <x-form.input label="{{ __('app.name_ar') }}" name="name_ar" :value="$bundle->name_ar" required maxlength="150" />
                    </div>
                    <div class="col-md-6 form-group">
                        <x-form.input label="{{ __('app.name_en') }}" name="name_en" :value="$bundle->name_en" required maxlength="150" />
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="description">{{ __('app.description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="2" maxlength="1000">{{ old('description', $bundle->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">{{ __('app.bundle_discounts') }}</div>
            <div class="card-body">
                <p class="text-muted">{{ __('app.bundle_discounts_help') }}</p>

                <div id="bundle-tiers">
                    @foreach (array_values($tiers) as $index => $tier)
                        <div class="row align-items-end bundle-tier-row">
                            <div class="col-md-4 form-group">
                                <label>{{ __('app.bundle_min_quantity') }}</label>
                                <input type="number" min="2" class="form-control" name="tiers[{{ $index }}][min_quantity]" value="{{ $tier['min_quantity'] ?? '' }}" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>{{ __('app.bundle_discount_type') }}</label>
                                <select class="form-control" name="tiers[{{ $index }}][discount_type]">
                                    <option value="percent" @selected(($tier['discount_type'] ?? '') === 'percent')>{{ __('app.bundle_type_percent') }}</option>
                                    <option value="fixed" @selected(($tier['discount_type'] ?? '') === 'fixed')>{{ __('app.bundle_type_fixed') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>{{ __('app.bundle_discount_value') }}</label>
                                <input type="number" min="0.01" step="0.01" class="form-control" name="tiers[{{ $index }}][discount_value]" value="{{ $tier['discount_value'] ?? '' }}" required>
                            </div>
                            <div class="col-md-2 form-group">
                                <button type="button" class="btn btn-outline-danger w-100" data-remove-tier>{{ __('app.delete') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" id="add-bundle-tier">{{ __('app.bundle_add_tier') }}</button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('app.products') }} (<span id="bundle-selected-count">{{ $selected->count() }}</span>)</span>
            </div>
            <div class="card-body">
                <input type="search" id="bundle-product-search" class="form-control mb-3" placeholder="{{ __('app.search') }}">
                <div class="bundle-product-list">
                    @foreach ($products as $product)
                        <label class="bundle-product-option" data-name="{{ mb_strtolower($product->name_ar.' '.$product->name_en) }}">
                            <input type="checkbox" name="products[]" value="{{ $product->id }}" @checked($selected->contains($product->id))>
                            <img src="{{ $product->image_url }}" alt="">
                            <span>{{ $product->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">{{ __('app.status') }}</div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $bundle->exists ? $bundle->is_active : true))>
                    <label class="custom-control-label" for="is_active">{{ __('app.active') }}</label>
                </div>
                <div class="custom-control custom-switch mb-3">
                    <input type="checkbox" class="custom-control-input" id="distinct_products" name="distinct_products" value="1" @checked(old('distinct_products', $bundle->distinct_products))>
                    <label class="custom-control-label" for="distinct_products">{{ __('app.bundle_distinct_products') }}</label>
                    <small class="text-muted d-block">{{ __('app.bundle_distinct_note') }}</small>
                </div>
                <div class="form-group">
                    <label for="starts_at">{{ __('app.bundle_starts_at') }}</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" class="form-control" value="{{ old('starts_at', $bundle->starts_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group mb-0">
                    <label for="ends_at">{{ __('app.bundle_ends_at') }}</label>
                    <input type="datetime-local" id="ends_at" name="ends_at" class="form-control" value="{{ old('ends_at', $bundle->ends_at?->format('Y-m-d\TH:i')) }}">
                    <small class="text-muted">{{ __('app.leave_empty_to_disable') }}</small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4">{{ __('app.save') }}</button>
    </div>
</div>

@push('script')
<script>
    (function () {
        const list = document.getElementById('bundle-tiers');
        let nextIndex = list.children.length;

        document.getElementById('add-bundle-tier').addEventListener('click', function () {
            const row = list.querySelector('.bundle-tier-row').cloneNode(true);

            row.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replace(/\[\d+\]/, '[' + nextIndex + ']');
                if (field.tagName === 'INPUT') field.value = '';
            });

            nextIndex++;
            list.appendChild(row);
        });

        list.addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-tier]') && list.children.length > 1) {
                event.target.closest('.bundle-tier-row').remove();
            }
        });

        document.getElementById('bundle-product-search').addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();

            document.querySelectorAll('.bundle-product-option').forEach(function (option) {
                option.classList.toggle('d-none', term !== '' && !option.dataset.name.includes(term));
            });
        });

        document.querySelector('.bundle-product-list').addEventListener('change', function () {
            document.getElementById('bundle-selected-count').textContent = this.querySelectorAll('input:checked').length;
        });
    })();
</script>
@endpush
