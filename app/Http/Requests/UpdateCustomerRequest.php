<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('customer')->id;

        return [
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($id)],
            'email'   => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'balance' => ['nullable', 'numeric'],
            'status'  => ['required', 'in:active,inactive'],
        ];
    }
}