<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email'   => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'balance' => ['nullable', 'numeric'],
            'status'  => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Customer name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.unique'   => 'A customer with this phone number already exists.',
        ];
    }
}