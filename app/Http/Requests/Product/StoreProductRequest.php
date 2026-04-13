<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ProductStatus;
use App\Enums\ProductType;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
    return $this->user()->can('create', Product::class);
    }

    protected function prepareForValidation()
    {
        $variations = collect($this->variations ?? [])
            ->filter(function ($variation) {
                return  !empty($variation['price']) ||
                        !empty($variation['compare_price']) ||
                        !empty($variation['stock']) ||
                        !empty($variation['sku']);
            })
            ->values()
            ->toArray();

        if (!empty($variations)) {
            $this->merge([
                'variations' => $variations
            ]);
        } else {
            $this->request->remove('variations'); // 🔥 امسحها خالص
        }


        if (empty($variations)) {
            $this->merge([
                'price' => $this->price !== '' ? $this->price : null,
                'compare_price' => $this->compare_price !== '' ? $this->compare_price : null,
                'stock' => $this->stock !== '' ? $this->stock : null,
                'sku' => $this->sku !== '' ? $this->sku : null,
            ]);
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        
        $rules = [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'status' => ['required', new Enum(ProductStatus::class)],
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'product_type' => ['required', new Enum(\App\Enums\ProductType::class)],

        ];

        if ($this->product_type === ProductType::SIMPLE->value) {

            $rules['price'] = 'required|numeric|min:0';
            $rules['compare_price'] = 'nullable|numeric|gt:price';
            $rules['stock'] = 'required|integer|min:0';

        } else {

            $rules['variations'] = 'required|array|min:1';
            $rules['variations.*.price'] = 'required|numeric|min:0';
            $rules['variations.*.compare_price'] = 'nullable|numeric';
            $rules['variations.*.stock'] = 'required|integer|min:0';
            $rules['variations.*.attribute_value_ids'] = 'required|array|min:1';
            $rules['variations.*.image'] = 'nullable|array';
            $rules['variations.*.image.*'] = 'image|mimes:jpg,jpeg,png,webp|max:2048';

        }

        return $rules;
    }


    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        dd($validator->errors()->toArray());
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->variations ?? [] as $index => $variation) {

                if (
                    isset($variation['compare_price']) &&
                    $variation['compare_price'] <= $variation['price']
                ) {
                    $validator->errors()->add(
                        "variations.$index.compare_price",
                        'سعر المقارنة يجب أن يكون أكبر من السعر'
                    );
                }
            }
        });
        if ($this->product_type === ProductType::SIMPLE->value) {

            if (
                $this->compare_price &&
                $this->compare_price <= $this->price
            ) {
                $validator->errors()->add(
                    "compare_price",
                    'سعر المقارنة يجب أن يكون أكبر من السعر'
                );
            }
        }

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
