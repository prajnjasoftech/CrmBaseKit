<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\FollowUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage follow ups') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'follow_up_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', Rule::in(array_keys(FollowUp::getStatuses()))],
        ];
    }
}
