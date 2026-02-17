<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage contact persons') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['boolean'],
        ];
    }
}
