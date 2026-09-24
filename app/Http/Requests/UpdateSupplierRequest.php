<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone'   => ['required', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'status'  => ['required', 'in:active,inactive'],
        ];
    }
}