<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'category_id'    => ['required', 'exists:categories,id'],
            'brand_id'       => ['required', 'exists:brands,id'],
            'supplier_id'    => ['nullable', 'exists:suppliers,id'],
            'name'           => ['required', 'string', 'max:200'],
            'sku'            => ['required', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($productId)],
            'model'          => ['nullable', 'string', 'max:120'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'quantity'       => ['required', 'integer', 'min:0'],
            'minimum_stock'  => ['required', 'integer', 'min:0'],
            'status'         => ['required', 'in:active,inactive'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}