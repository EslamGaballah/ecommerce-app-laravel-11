
<div class="form-group">
    <x-form.input label="Role Name" class="form-control-lg" role="input" name="name" :value="$role->name" />
</div>

<fieldset>
    <legend class="fw-bold mb-4">{{ __('Permissions') }}</legend>

    <div class="row">
        @foreach ($permissions as $groupName => $groupItems)
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    {{-- عنوان المجموعة سيكون الكلمة الأخيرة (مثل: User, Category) --}}
                    <div class="card-header bg-primary text-white text-capitalize fw-bold">
                        {{ $groupName }}
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            @foreach ($groupItems as $permission)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check custom-checkbox">
                                        <input class="form-check-input" type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               id="perm-{{ $permission->id }}"
                                               @checked(
                                                    isset($role) && $role->permissions->contains($permission->id) ||
                                                    collect(old('permissions'))->contains($permission->id)

                                                    )>
                                        
                                        <label class="form-check-label text-capitalize" for="perm-{{ $permission->id }}">
                                            <!-- {{ explode('-', $permission->name)[0] }} -->
                                            <!-- {{ str_replace('-' . $groupName, '', $permission->name) }} -->
                                              {{ str_replace('-', ' ', str_replace('-' . $groupName, '', $permission->name)) }}
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