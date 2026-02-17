<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('convert leads') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'status' => ['sometimes', Rule::in(array_keys(Customer::getStatuses()))],
            'notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'business_id' => ['nullable', 'exists:businesses,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The customer name is required.',
        ];
    }

    /**
     * Prepare data for validation by pre-filling from lead.
     */
    protected function prepareForValidation(): void
    {
        /** @var Lead|null $lead */
        $lead = $this->route('lead');

        if ($lead && ! $this->filled('name')) {
            $this->merge([
                'name' => $lead->name,
                'email' => $this->input('email', $lead->email),
                'phone' => $this->input('phone', $lead->phone),
                'company' => $this->input('company', $lead->company),
                'notes' => $this->input('notes', $lead->notes),
                'assigned_to' => $this->input('assigned_to', $lead->assigned_to),
                'business_id' => $this->input('business_id', $lead->business_id),
            ]);
        }
    }
}
