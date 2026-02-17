<?php

namespace App\Http\Requests\Product;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'status' => ['required', new Enum(ProductStatus::class)],
            'category_id' => 'required|exists:categories,id',
            
            'variations' => 'required|array|min:1',

            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.compare_price' => 'nullable|numeric',
            'variations.*.quantity' => 'required|integer|min:0',
            'variations.*.sku' => 'required|string',

            'variations.*.attributes' => 'required|array|min:1',
            'variations.*.attributes.*' => 'exists:attribute_values,id',

            'variations.*.image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
}
