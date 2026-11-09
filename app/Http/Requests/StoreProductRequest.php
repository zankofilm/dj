<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'integer', 'exists:sellers,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'product_type' => ['required', Rule::in(['physical', 'digital', 'service'])],
            'condition' => ['required', Rule::in(['new', 'used', 'refurbished'])],
            'currency' => ['required', 'string', 'size:3'],
            'base_price_minor' => ['required', 'integer', 'min:0'],
            'brand_name' => ['nullable', 'string', 'max:120'],
            'translations' => ['required', 'array'],
            'translations.fa' => ['required', 'array'],
            'translations.fa.title' => ['required', 'string', 'max:190'],
            'translations.fa.description' => ['nullable', 'string', 'max:30000'],
            'translations.ku' => ['nullable', 'array'],
            'translations.en' => ['nullable', 'array'],
            'attributes' => ['nullable', 'array'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [
                'required', 'file', 'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
            ],
        ];
    }
}
