<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EntityType;
use App\Http\Requests\ConvertLeadRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lead::class);

        $search = $request->input('search');

        $leads = Lead::query()
            ->with(['assignee:id,name', 'business:id,name'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'statuses' => Lead::getStatuses(),
            'sources' => Lead::getSources(),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Lead::class);

        return Inertia::render('Leads/Create', [
            'statuses' => Lead::getStatuses(),
            'sources' => Lead::getSources(),
            'entityTypes' => EntityType::toArray(),
            'users' => $this->getAssignableUsers(),
            'businesses' => $this->getBusinesses(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        Lead::create($request->validated());

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): Response
    {
        $this->authorize('view', $lead);

        $lead->load(['assignee:id,name', 'business:id,name', 'customer', 'contactPeople']);

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'statuses' => Lead::getStatuses(),
            'sources' => Lead::getSources(),
            'entityTypes' => EntityType::toArray(),
        ]);
    }

    public function edit(Lead $lead): Response
    {
        $this->authorize('update', $lead);

        $lead->load('contactPeople');

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'statuses' => Lead::getStatuses(),
            'sources' => Lead::getSources(),
            'entityTypes' => EntityType::toArray(),
            'users' => $this->getAssignableUsers(),
            'businesses' => $this->getBusinesses(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $lead->update($request->validated());

        return redirect()->route('leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    public function showConvert(Lead $lead): Response
    {
        $this->authorize('convert', $lead);

        $lead->load('contactPeople');

        return Inertia::render('Leads/Convert', [
            'lead' => $lead,
            'customerStatuses' => Customer::getStatuses(),
            'entityTypes' => EntityType::toArray(),
            'users' => $this->getAssignableUsers(),
            'businesses' => $this->getBusinesses(),
            'countries' => $this->getCountries(),
        ]);
    }

    public function convert(ConvertLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('convert', $lead);

        $data = $request->validated();
        $data['converted_from_lead_id'] = $lead->id;
        $data['entity_type'] = $lead->entity_type;

        $customer = Customer::create($data);

        // Copy contact people from lead to customer
        foreach ($lead->contactPeople as $contact) {
            $customer->contactPeople()->create([
                'name' => $contact->name,
                'email' => $contact->email,
                'mobile' => $contact->mobile,
                'designation' => $contact->designation,
                'is_primary' => $contact->is_primary,
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Lead converted to customer successfully.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getAssignableUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Business>
     */
    private function getBusinesses(): \Illuminate\Database\Eloquent\Collection
    {
        return Business::query()
            ->select(['id', 'name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    private function getCountries(): array
    {
        return [
            'US' => 'United States',
            'UK' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'NL' => 'Netherlands',
            'SG' => 'Singapore',
        ];
    }
}
