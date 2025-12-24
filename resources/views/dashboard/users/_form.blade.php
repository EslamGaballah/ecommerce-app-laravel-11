
<div class="form-group">
    <label for="">Name</label>
    <x-form.input 
        label="name" 
        class="form-control-lg" 
        name="name" 
        :value="old('name', $user->name ?? '')"
        />
</div>

<!-- Email Address -->
<div class="form-group">
    <label for="">Email</label>
    <x-form.input 
        label="Email" 
        type="email" 
        name="email" 
        :value="old('email', $user->email ?? '')"
        />

</div>

<!-- Password -->
<div class="form-group">
    <label for="">password</label>
    <x-form.input 
        label="password" 
        class="form-control-lg" 
        name="password" 
        type="password" />
</div>

 <!-- Confirm Password -->
<div class="form-group">
    <label for="">Confirm Password</label>
    <x-form.input 
        label="password" 
        class="form-control-lg" 
        name="password_confirmation" 
        type="password" required autocomplete="new-password" />

    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
</div>

<fieldset>
    <legend>{{ __('Roles') }}</legend>

    @foreach ($roles as $role)
    <div class="form-check">
        <input 
            {{-- class="form-check-input"  --}}
            type="radio" 
            name="role_id" 
            value="{{ $role->id }}" 
            {{-- @checked(in_array($role->id, old('roles', $user)))> --}}
                   @checked(old('role_id', $user->role_id) == $role->id)
                   >


        <label class="form-check-label">
            {{ $role->name }}
        </label>
    </div>
    @endforeach
</fieldset>

<div class="form-group">
    <button type="submit" class="btn btn-primary">{{ $button_label ?? 'Save' }}</button>
</div>