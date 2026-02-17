<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactPersonRequest;
use App\Http\Requests\UpdateContactPersonRequest;
use App\Models\ContactPerson;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\ContactPersonService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactPersonController extends Controller
{
    public function __construct(
        private readonly ContactPersonService $contactPersonService
    ) {}

    // Lead contact person methods
    public function createForLead(Lead $lead): Response
    {
        $this->authorize('create', [ContactPerson::class, $lead]);

        return Inertia::render('ContactPeople/Create', [
            'parent' => $lead,
            'parentType' => 'lead',
        ]);
    }

    public function storeForLead(StoreContactPersonRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('create', [ContactPerson::class, $lead]);

        $this->contactPersonService->addContact($lead, $request->validated());

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Contact person added successfully.');
    }

    public function editForLead(Lead $lead, ContactPerson $contact): Response
    {
        $this->authorize('update', $contact);

        return Inertia::render('ContactPeople/Edit', [
            'parent' => $lead,
            'parentType' => 'lead',
            'contact' => $contact,
        ]);
    }

    public function updateForLead(UpdateContactPersonRequest $request, Lead $lead, ContactPerson $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        $this->contactPersonService->updateContact($contact, $request->validated());

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Contact person updated successfully.');
    }

    public function destroyForLead(Lead $lead, ContactPerson $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $this->contactPersonService->deleteContact($contact);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Contact person deleted successfully.');
    }

    public function setPrimaryForLead(Lead $lead, ContactPerson $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        $this->contactPersonService->setPrimary($contact);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Primary contact updated successfully.');
    }

    // Customer contact person methods
    public function createForCustomer(Customer $customer): Response
    {
        $this->authorize('create', [ContactPerson::class, $customer]);

        return Inertia::render('ContactPeople/Create', [
            'parent' => $customer,
            'parentType' => 'customer',
        ]);
    }

    public function storeForCustomer(StoreContactPersonRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('create', [ContactPerson::class, $customer]);

        $this->contactPersonService->addContact($customer, $request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Contact person added successfully.');
    }

    public function editForCustomer(Customer $customer, ContactPerson $contact): Response
    {
        $this->authorize('update', $contact);

        return Inertia::render('ContactPeople/Edit', [
            'parent' => $customer,
            'parentType' => 'customer',
            'contact' => $contact,
        ]);
    }

    public function updateForCustomer(UpdateContactPersonRequest $request, Customer $customer, ContactPerson $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        $this->contactPersonService->updateContact($contact, $request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Contact person updated successfully.');
    }

    public function destroyForCustomer(Customer $customer, ContactPerson $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $this->contactPersonService->deleteContact($contact);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Contact person deleted successfully.');
    }

    public function setPrimaryForCustomer(Customer $customer, ContactPerson $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        $this->contactPersonService->setPrimary($contact);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Primary contact updated successfully.');
    }
}
