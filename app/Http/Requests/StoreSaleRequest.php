<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Staff AND admin can create sales
        return $this->user() && $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'customer_id'         => ['nullable', 'exists:customers,id'],
            'discount'            => ['nullable', 'numeric', 'min:0'],
            'paid'                => ['required', 'numeric', 'min:0'],
            'payment_method'      => ['required', 'in:cash,bank,credit,other'],
            'status'              => ['required', 'in:completed,pending,cancelled'],
            'notes'               => ['nullable', 'string', 'max:500'],

            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'items.*.price'       => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Add at least one product to the sale.',
            'items.*.product_id.required' => 'Please select a product for each row.',
            'items.*.quantity.min'        => 'Quantity must be at least 1.',
        ];
    }
}