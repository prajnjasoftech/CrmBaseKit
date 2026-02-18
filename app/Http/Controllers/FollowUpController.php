<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\UpdateFollowUpRequest;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Services\FollowUpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowUpController extends Controller
{
    public function __construct(
        private readonly FollowUpService $followUpService
    ) {}

    // Lead follow-up methods
    public function createForLead(Lead $lead): Response
    {
        $this->authorize('create', [FollowUp::class, $lead]);

        return Inertia::render('FollowUps/Create', [
            'parent' => $lead,
            'parentType' => 'lead',
            'statuses' => FollowUp::getStatuses(),
        ]);
    }

    public function storeForLead(StoreFollowUpRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('create', [FollowUp::class, $lead]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->followUpService->addFollowUp($lead, $request->validated(), $user);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up scheduled successfully.');
    }

    public function editForLead(Lead $lead, FollowUp $followUp): Response
    {
        $this->authorize('update', $followUp);

        return Inertia::render('FollowUps/Edit', [
            'parent' => $lead,
            'parentType' => 'lead',
            'followUp' => $followUp,
            'statuses' => FollowUp::getStatuses(),
        ]);
    }

    public function updateForLead(UpdateFollowUpRequest $request, Lead $lead, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('update', $followUp);

        $this->followUpService->updateFollowUp($followUp, $request->validated());

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up updated successfully.');
    }

    public function destroyForLead(Lead $lead, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('delete', $followUp);

        $this->followUpService->deleteFollowUp($followUp);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up deleted successfully.');
    }

    public function completeForLead(Request $request, Lead $lead, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('complete', $followUp);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->followUpService->markCompleted($followUp, $user);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Follow-up marked as completed.');
    }

    // Customer follow-up methods
    public function createForCustomer(Customer $customer): Response
    {
        $this->authorize('create', [FollowUp::class, $customer]);

        return Inertia::render('FollowUps/Create', [
            'parent' => $customer,
            'parentType' => 'customer',
            'statuses' => FollowUp::getStatuses(),
        ]);
    }

    public function storeForCustomer(StoreFollowUpRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('create', [FollowUp::class, $customer]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->followUpService->addFollowUp($customer, $request->validated(), $user);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Follow-up scheduled successfully.');
    }

    public function editForCustomer(Customer $customer, FollowUp $followUp): Response
    {
        $this->authorize('update', $followUp);

        return Inertia::render('FollowUps/Edit', [
            'parent' => $customer,
            'parentType' => 'customer',
            'followUp' => $followUp,
            'statuses' => FollowUp::getStatuses(),
        ]);
    }

    public function updateForCustomer(UpdateFollowUpRequest $request, Customer $customer, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('update', $followUp);

        $this->followUpService->updateFollowUp($followUp, $request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Follow-up updated successfully.');
    }

    public function destroyForCustomer(Customer $customer, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('delete', $followUp);

        $this->followUpService->deleteFollowUp($followUp);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Follow-up deleted successfully.');
    }

    public function completeForCustomer(Request $request, Customer $customer, FollowUp $followUp): RedirectResponse
    {
        $this->authorize('complete', $followUp);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->followUpService->markCompleted($followUp, $user);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Follow-up marked as completed.');
    }
}
