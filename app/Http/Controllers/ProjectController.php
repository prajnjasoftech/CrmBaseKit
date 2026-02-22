<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function create(Customer $customer): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Projects/Create', [
            'customer' => $customer->only(['id', 'name']),
            'services' => $this->getServices(),
            'users' => $this->getAssignableUsers(),
            'statuses' => Project::getStatuses(),
            'currentUserId' => Auth::id(),
        ]);
    }

    public function store(StoreProjectRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $data = $request->validated();
        $data['customer_id'] = $customer->id;
        $data['created_by'] = Auth::id();

        Project::create($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Project created successfully.');
    }

    public function show(Customer $customer, Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['service:id,name', 'assignee:id,name', 'creator:id,name']);

        return Inertia::render('Projects/Show', [
            'customer' => $customer->only(['id', 'name']),
            'project' => $project,
            'statuses' => Project::getStatuses(),
        ]);
    }

    public function edit(Customer $customer, Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'customer' => $customer->only(['id', 'name']),
            'project' => $project,
            'services' => $this->getServices(),
            'users' => $this->getAssignableUsers(),
            'statuses' => Project::getStatuses(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Customer $customer, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Customer $customer, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Service>
     */
    private function getServices(): \Illuminate\Database\Eloquent\Collection
    {
        return Service::query()
            ->select(['id', 'name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
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
}
