<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EntityType;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        $search = $request->string('search')->toString();

        $customers = Customer::query()
            ->with(['assignee:id,name', 'business:id,name'])
            ->when($search !== '', function ($query) use ($search) {
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

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'statuses' => Customer::getStatuses(),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('Customers/Create', [
            'statuses' => Customer::getStatuses(),
            'entityTypes' => EntityType::toArray(),
            'users' => $this->getAssignableUsers(),
            'businesses' => $this->getBusinesses(),
            'countries' => $this->getCountries(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        Customer::create($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        $customer->load(['assignee:id,name', 'business:id,name', 'lead', 'contactPeople']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'statuses' => Customer::getStatuses(),
            'entityTypes' => EntityType::toArray(),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        $customer->load('contactPeople');

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'statuses' => Customer::getStatuses(),
            'entityTypes' => EntityType::toArray(),
            'users' => $this->getAssignableUsers(),
            'businesses' => $this->getBusinesses(),
            'countries' => $this->getCountries(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
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
