<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSellerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:190'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ];
    }
}
