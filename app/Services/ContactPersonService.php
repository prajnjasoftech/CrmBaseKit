<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EntityType;
use App\Exceptions\InvalidContactPersonException;
use App\Models\ContactPerson;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class ContactPersonService
{
    /**
     * Add a contact person to a lead or customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function addContact(Lead|Customer $entity, array $data): ContactPerson
    {
        if ($entity->entity_type !== EntityType::Business) {
            throw InvalidContactPersonException::notBusinessEntity();
        }

        return DB::transaction(function () use ($entity, $data): ContactPerson {
            $isPrimary = $data['is_primary'] ?? false;

            if ($isPrimary) {
                $this->unsetExistingPrimary($entity);
            }

            /** @var ContactPerson $contact */
            $contact = $entity->contactPeople()->create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'designation' => $data['designation'] ?? null,
                'is_primary' => $isPrimary,
            ]);

            return $contact;
        });
    }

    /**
     * Update an existing contact person.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateContact(ContactPerson $contact, array $data): ContactPerson
    {
        return DB::transaction(function () use ($contact, $data): ContactPerson {
            $isPrimary = $data['is_primary'] ?? $contact->is_primary;

            if ($isPrimary && ! $contact->is_primary) {
                $entity = $contact->contactable;
                if ($entity instanceof Lead || $entity instanceof Customer) {
                    $this->unsetExistingPrimary($entity);
                }
            }

            $contact->update([
                'name' => $data['name'] ?? $contact->name,
                'email' => array_key_exists('email', $data) ? $data['email'] : $contact->email,
                'mobile' => array_key_exists('mobile', $data) ? $data['mobile'] : $contact->mobile,
                'designation' => array_key_exists('designation', $data) ? $data['designation'] : $contact->designation,
                'is_primary' => $isPrimary,
            ]);

            return $contact->fresh() ?? $contact;
        });
    }

    /**
     * Delete a contact person.
     */
    public function deleteContact(ContactPerson $contact): void
    {
        $contact->delete();
    }

    /**
     * Set a contact person as the primary contact.
     */
    public function setPrimary(ContactPerson $contact): void
    {
        DB::transaction(function () use ($contact): void {
            $entity = $contact->contactable;
            if ($entity instanceof Lead || $entity instanceof Customer) {
                $this->unsetExistingPrimary($entity);
            }

            $contact->update(['is_primary' => true]);
        });
    }

    /**
     * Unset the existing primary contact for an entity.
     */
    private function unsetExistingPrimary(Lead|Customer $entity): void
    {
        $entity->contactPeople()
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }
}
