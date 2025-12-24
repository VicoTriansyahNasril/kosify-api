<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'identification_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}