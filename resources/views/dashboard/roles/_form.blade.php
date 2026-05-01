
<div class="form-group">
    <x-form.input label="{{__('app.name')}}" class="form-control-lg" role="input" name="name" :value="$role->name" />
</div>

<fieldset>
    <legend class="fw-bold mb-4">{{ __('app.permissions_management') }}</legend>

    <div class="row">
        @foreach ($permissions as $groupName => $groupItems)
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-primary permission-group">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span class="text-capitalize fw-bold">
                            {{-- استخدام trans()->has() للتأكد من وجود الترجمة --}}
                            {{trans()->has('permission_labels.groups.' . $groupName) ? __('permission_labels.groups.' . $groupName) : ucfirst($groupName) }}
                        </span>
                        
                        <div class="form-check mb-0">
                            @php
                                // التحقق مما إذا كانت كل صلاحيات المجموعة مختارة مسبقاً
                                $allChecked = $groupItems->every(fn($p) => $role->permissions->contains($p->id));
                            @endphp
                            <input class="form-check-input select-all-group" type="checkbox" 
                                   id="select-all-{{ $groupName }}" {{ $allChecked ? 'checked' : '' }}>
                            <label class="form-check-label text-white small" for="select-all-{{ $groupName }}">
                                {{ __('Select All') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            @foreach ($groupItems as $permission)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               id="perm-{{ $permission->id }}"
                                               @checked(
                                                   (isset($role) && $role->permissions->contains($permission->id)) ||
                                                   (is_array(old('permissions')) && in_array($permission->id, old('permissions')))
                                               )>

                                        <label class="form-check-label" for="perm-{{ $permission->id }}">
                                            @php
                                                $parts = explode('-', $permission->name);
                                                $action = $parts[0] ?? '';
                                            @endphp
                                            {{ trans()->has('permission_labels.actions.' . $action) ? __('permission_labels.actions.' . $action) : ucwords(str_replace('-', ' ', $action)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</fieldset>

<div class="form-group">
    <button type="submit" class="btn btn-primary">{{ $button_label ?? 'Save' }}</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // الحصول على جميع أزرار "Select All"
    const selectAllCheckboxes = document.querySelectorAll('.select-all-group');

    selectAllCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            // البحث عن أقرب حاوية (Card) ثم البحث عن الـ checkboxes بداخلها فقط
            const group = this.closest('.permission-group');
            const checkboxes = group.querySelectorAll('.permission-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });

    // تحديث حالة "Select All" تلقائياً إذا تم اختيار العناصر يدوياً
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const group = this.closest('.permission-group');
            const selectAll = group.querySelector('.select-all-group');
            const allInGroup = group.querySelectorAll('.permission-checkbox');
            const allCheckedInGroup = group.querySelectorAll('.permission-checkbox:checked');
            
            selectAll.checked = (allInGroup.length === allCheckedInGroup.length);
        });
    });
});
</script>