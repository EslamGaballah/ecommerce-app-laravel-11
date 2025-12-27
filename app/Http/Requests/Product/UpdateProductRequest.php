<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');

        return auth()->user()->can('update', $product);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
             'description' => 'nullable|string|max:255',
             'status' => 'in:active,archived,draft',
             'category_id' => 'required|exists:categories,id',
             'price' => 'required|numeric|min:0',
             'compare_price' => 'nullable|numeric|gt:price',
             'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
}
