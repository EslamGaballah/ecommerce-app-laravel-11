<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('category');

        return auth()->user()->can('update', $category);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'name_ar' => 'required|string|max:255',
                'name_en' => ['required', 'string', 'max:255', Rule::unique('categories', 'name_en')->ignore($this->route('category'))],
                'description' => 'nullable|string|max:255',
                'status' => 'in:active,archived',
                'parent_id' => 'nullable|exists:categories,id'
        ];
    }
}
