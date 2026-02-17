<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Business;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit businesses') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Business $business */
        $business = $this->route('business');
        $businessId = $business->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:50', Rule::unique('businesses')->ignore($businessId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('businesses')->ignore($businessId)],
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
