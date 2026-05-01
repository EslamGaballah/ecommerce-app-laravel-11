<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       $role = $this->route('role');

        return auth()->user()->can('update', $role);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('roles', 'name')->ignore($this->route('role')->id),
        ],
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ];
    }
}
