<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EntityType;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create leads') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['required', Rule::enum(EntityType::class)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['required', Rule::in(array_keys(Lead::getSources()))],
            'status' => ['sometimes', Rule::in(array_keys(Lead::getStatuses()))],
            'notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'business_id' => ['nullable', 'exists:businesses,id'],
        ];
    }
}
