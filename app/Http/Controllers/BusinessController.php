<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Business::class);

        $businesses = Business::query()
            ->with('creator:id,name')
            ->latest()
            ->paginate(15);

        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Business::class);

        return Inertia::render('Businesses/Create', [
            'industries' => $this->getIndustries(),
            'countries' => $this->getCountries(),
        ]);
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $this->authorize('create', Business::class);

        $data = $request->validated();
        $data['created_by'] = Auth::id();

        Business::create($data);

        return redirect()->route('businesses.index')
            ->with('success', 'Business created successfully.');
    }

    public function show(Business $business): Response
    {
        $this->authorize('view', $business);

        $business->load('creator:id,name');

        return Inertia::render('Businesses/Show', [
            'business' => $business,
        ]);
    }

    public function edit(Business $business): Response
    {
        $this->authorize('update', $business);

        return Inertia::render('Businesses/Edit', [
            'business' => $business,
            'industries' => $this->getIndustries(),
            'countries' => $this->getCountries(),
        ]);
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('update', $business);

        $business->update($request->validated());

        return redirect()->route('businesses.index')
            ->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $this->authorize('delete', $business);

        $business->delete();

        return redirect()->route('businesses.index')
            ->with('success', 'Business deleted successfully.');
    }

    /**
     * @return array<string>
     */
    private function getIndustries(): array
    {
        return [
            'Technology',
            'Healthcare',
            'Finance',
            'Retail',
            'Manufacturing',
            'Education',
            'Real Estate',
            'Consulting',
            'Transportation',
            'Energy',
            'Media',
            'Other',
        ];
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
