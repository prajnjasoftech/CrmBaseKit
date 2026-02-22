<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Service::class);

        $search = $request->string('search')->toString();

        $services = Service::query()
            ->with('creator:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Service::class);

        return Inertia::render('Services/Create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Service::class);

        $data = $request->validated();
        $data['created_by'] = Auth::id();

        Service::create($data);

        return redirect()->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    public function show(Service $service): Response
    {
        $this->authorize('view', $service);

        $service->load('creator:id,name');

        return Inertia::render('Services/Show', [
            'service' => $service,
        ]);
    }

    public function edit(Service $service): Response
    {
        $this->authorize('update', $service);

        return Inertia::render('Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $service->update($request->validated());

        return redirect()->route('services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
