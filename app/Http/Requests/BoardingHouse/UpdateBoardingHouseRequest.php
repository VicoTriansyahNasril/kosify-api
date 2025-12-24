<?php

namespace App\Http\Requests\BoardingHouse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardingHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('boarding_house')->user_id;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string'],
        ];
    }
}