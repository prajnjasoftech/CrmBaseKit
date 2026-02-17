<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create businesses') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:50', 'unique:businesses'],
            'email' => ['required', 'email', 'max:255', 'unique:businesses'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'industry' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive,pending'],
        ];
    }
}
