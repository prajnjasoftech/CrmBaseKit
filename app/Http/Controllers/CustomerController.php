<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->with(['assignee:id,name', 'business:id,name'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'statuses' => Customer::getStatuses(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('Customers/Create', [
            'statuses' => Customer::getStatuses(),
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

        $customer->load(['assignee:id,name', 'business:id,name', 'lead']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'statuses' => Customer::getStatuses(),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'statuses' => Customer::getStatuses(),
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
