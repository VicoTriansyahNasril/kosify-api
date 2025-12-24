<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Room;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => [
                'required',
                'exists:rooms,id',
                function ($attribute, $value, $fail) {
                    $room = Room::find($value);
                    if ($room && $room->status->value !== 'available') {
                        $fail('Kamar ini sudah terisi atau sedang perbaikan.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'identification_number' => ['nullable', 'string', 'max:50'], 
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'entry_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:entry_date'],
        ];
    }
}