<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product)],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],

            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
            'delete_image_ids' => ['array'],
            'delete_image_ids.*' => [Rule::exists('product_images', 'id')->where('product_id', $product->id)],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $product->id)],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.attribute_value_ids' => ['array'],
            'variants.*.attribute_value_ids.*' => [Rule::exists('attribute_values', 'id')],
        ];
    }
}
