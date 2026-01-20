<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ProductStatus;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
    return $this->user()->can('create', Product::class);
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
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
            'quantity' => 'required|numeric|min:0',
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'

        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم المنتج مطلوب',
            'price.required' => 'السعر مطلوب',
            'price.numeric'  => 'السعر يجب أن يكون رقم',
        ];
    }
}
