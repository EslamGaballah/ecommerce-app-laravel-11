
<div class="form-group">
    <x-form.input label="Role Name" class="form-control-lg" role="input" name="name" :value="$role->name" />
</div>

<fieldset>
    <legend>{{ __('Permissions') }}</legend>

    @foreach ($permissions as $permission)
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <span class="text-capitalize">{{ str_replace('-', ' ', $permission->name) }}</span>
            {{-- {{ $permission->name }} --}}
        </div>

        <div class="col-md-4">
            <div class="form-check form-check-inline">            
               <input class="form-check-input" type="checkbox" 
                    name="permissions[]"
                    value="{{ $permission->id }}"
                    id="perm-{{ $permission->id }}"
                    @checked(
                            isset($role) && $role->permissions->contains($permission->id) || 
                            collect(old('permissions'))->contains($permission->id)
                    )>
                <label class="form-check-label" for="perm-{{ $permission->id }}">
                    {{ __('Allow') }}
                </label>
            </div>
        </div>
    </div>
    @endforeach
</fieldset>

<div class="form-group">
    <button type="submit" class="btn btn-primary">{{ $button_label ?? 'Save' }}</button>
</div>