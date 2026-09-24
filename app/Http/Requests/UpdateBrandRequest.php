<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')->id;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'name.unique'   => 'A brand with this name already exists.',
        ];
    }
}