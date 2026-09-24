<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'supplier_id'             => ['required', 'exists:suppliers,id'],
            'discount'                => ['nullable', 'numeric', 'min:0'],
            'paid'                    => ['required', 'numeric', 'min:0'],
            'payment_method'          => ['required', 'in:cash,bank,other'],
            'status'                  => ['required', 'in:pending,received,cancelled'],
            'notes'                   => ['nullable', 'string', 'max:500'],

            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'exists:products,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.purchase_price'  => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'        => 'Please select a supplier.',
            'items.required'              => 'Add at least one product to the purchase.',
            'items.*.product_id.required' => 'Please select a product for each row.',
            'items.*.quantity.min'        => 'Quantity must be at least 1.',
        ];
    }
}