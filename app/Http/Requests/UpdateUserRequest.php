<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone'    => ['nullable', 'string', 'max:30'],
            'role_id'  => ['required', 'exists:roles,id'],
            'status'   => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ];
    }
}