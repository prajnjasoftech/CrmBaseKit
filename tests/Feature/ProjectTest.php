<?php

use App\Models\Customer;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Project Create', function (): void {
    it('shows create form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        Service::factory()->count(3)->active()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/create");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Create')
            ->has('customer')
            ->has('services', 3)
            ->has('users')
            ->has('statuses')
            ->has('currentUserId')
        );
    });

    it('only shows active services in create form', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        Service::factory()->count(2)->active()->create();
        Service::factory()->count(1)->inactive()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/create");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Create')
            ->has('services', 2)
        );
    });

    it('stores project with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Website Redesign',
            'description' => 'Complete website overhaul',
            'service_id' => $service->id,
            'status' => 'pending',
            'start_date' => '2026-03-01',
            'end_date' => '2026-06-01',
            'budget' => 15000,
            'assigned_to' => $user->id,
        ]);

        $response->assertRedirect("/customers/{$customer->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'name' => 'Website Redesign',
            'description' => 'Complete website overhaul',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
        ]);
    });

    it('stores project with minimal data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Quick Project',
            'service_id' => $service->id,
        ]);

        $response->assertRedirect("/customers/{$customer->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'name' => 'Quick Project',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", []);

        $response->assertSessionHasErrors(['name', 'service_id']);
    });

    it('validates name max length', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => str_repeat('a', 256),
            'service_id' => $service->id,
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates status enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Test Project',
            'service_id' => $service->id,
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    });

    it('validates service exists', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Test Project',
            'service_id' => 999,
        ]);

        $response->assertSessionHasErrors(['service_id']);
    });

    it('validates end_date is after start_date', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Test Project',
            'service_id' => $service->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-03-01',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    });

    it('validates budget is positive', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Test Project',
            'service_id' => $service->id,
            'budget' => -100,
        ]);

        $response->assertSessionHasErrors(['budget']);
    });

    it('denies create to unauthorized user', function (): void {
        $user = User::factory()->create();
        // User without any role/permission

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/projects", [
            'name' => 'Test Project',
        ]);

        $response->assertStatus(403);
    });

    it('denies access to unauthenticated user', function (): void {
        $customer = Customer::factory()->create();

        $response = $this->get("/customers/{$customer->id}/projects/create");

        $response->assertRedirect('/login');
    });
});

describe('Project Show', function (): void {
    it('shows project details to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->create();
        $project = Project::factory()->forCustomer($customer)->forService($service)->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('customer')
            ->has('project')
            ->where('project.id', $project->id)
            ->has('statuses')
        );
    });

    it('loads service relationship', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['name' => 'Web Development']);
        $project = Project::factory()->forCustomer($customer)->forService($service)->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.service.name', 'Web Development')
        );
    });

    it('loads assignee relationship', function (): void {
        $assignee = User::factory()->create(['name' => 'John Assignee']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->assignedTo($assignee)->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.assignee.name', 'John Assignee')
        );
    });

    it('loads creator relationship', function (): void {
        $creator = User::factory()->create(['name' => 'Jane Creator']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->state(['created_by' => $creator->id])->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.creator.name', 'Jane Creator')
        );
    });

    it('returns 404 for non-existent project', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/999");

        $response->assertStatus(404);
    });
});

describe('Project Edit', function (): void {
    it('shows edit form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Edit')
            ->has('customer')
            ->has('project')
            ->has('services')
            ->has('users')
            ->has('statuses')
        );
    });

    it('updates project with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => 'Updated Project Name',
            'description' => 'Updated description',
            'service_id' => $service->id,
            'status' => 'in_progress',
            'start_date' => '2026-04-01',
            'end_date' => '2026-08-01',
            'budget' => 25000,
            'assigned_to' => $user->id,
        ]);

        $response->assertRedirect("/customers/{$customer->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
            'description' => 'Updated description',
            'status' => 'in_progress',
        ]);
    });

    it('validates required fields on update', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => '',
            'service_id' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'service_id']);
    });

    it('denies edit to unauthorized user', function (): void {
        $user = User::factory()->create();
        // User without any role/permission

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    });
});

describe('Project Delete', function (): void {
    it('deletes project', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->delete("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertRedirect("/customers/{$customer->id}");
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    });

    it('denies delete to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->delete("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertStatus(403);
    });

    it('denies delete to manager role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $response = $this->actingAs($user)->delete("/customers/{$customer->id}/projects/{$project->id}");

        $response->assertStatus(403);
    });
});

describe('Project Authorization', function (): void {
    it('allows admin full access', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $this->actingAs($user)->get("/customers/{$customer->id}/projects/create")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}/edit")->assertStatus(200);
    });

    it('allows manager to view, create, and edit but not delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $this->actingAs($user)->get("/customers/{$customer->id}/projects/create")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}/edit")->assertStatus(200);
        $this->actingAs($user)->delete("/customers/{$customer->id}/projects/{$project->id}")->assertStatus(403);
    });

    it('allows sales to view, create, and edit but not delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/create")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}/edit")->assertStatus(200);
        $this->actingAs($user)->delete("/customers/{$customer->id}/projects/{$project->id}")->assertStatus(403);
    });

    it('denies user role all access', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $this->actingAs($user)->get("/customers/{$customer->id}/projects/create")->assertStatus(403);
        $this->actingAs($user)->get("/customers/{$customer->id}/projects/{$project->id}")->assertStatus(403);
    });
});

describe('Project Model', function (): void {
    it('has customer relationship', function (): void {
        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        expect($project->customer->id)->toBe($customer->id);
    });

    it('has service relationship', function (): void {
        $service = Service::factory()->create();
        $project = Project::factory()->forService($service)->create();

        expect($project->service->id)->toBe($service->id);
    });

    it('has assignee relationship', function (): void {
        $assignee = User::factory()->create();
        $project = Project::factory()->assignedTo($assignee)->create();

        expect($project->assignee->id)->toBe($assignee->id);
    });

    it('has creator relationship', function (): void {
        $creator = User::factory()->create();
        $project = Project::factory()->state(['created_by' => $creator->id])->create();

        expect($project->creator->id)->toBe($creator->id);
    });

    it('uses soft deletes', function (): void {
        $project = Project::factory()->create();
        $project->delete();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        expect(Project::withTrashed()->find($project->id))->not->toBeNull();
    });

    it('isActive returns true for active statuses', function (): void {
        $pendingProject = Project::factory()->pending()->create();
        $inProgressProject = Project::factory()->inProgress()->create();
        $onHoldProject = Project::factory()->onHold()->create();

        expect($pendingProject->isActive())->toBeTrue();
        expect($inProgressProject->isActive())->toBeTrue();
        expect($onHoldProject->isActive())->toBeTrue();
    });

    it('isActive returns false for inactive statuses', function (): void {
        $completedProject = Project::factory()->completed()->create();
        $cancelledProject = Project::factory()->cancelled()->create();

        expect($completedProject->isActive())->toBeFalse();
        expect($cancelledProject->isActive())->toBeFalse();
    });

    it('isCompleted returns true for completed status', function (): void {
        $completedProject = Project::factory()->completed()->create();
        $pendingProject = Project::factory()->pending()->create();

        expect($completedProject->isCompleted())->toBeTrue();
        expect($pendingProject->isCompleted())->toBeFalse();
    });

    it('isCancelled returns true for cancelled status', function (): void {
        $cancelledProject = Project::factory()->cancelled()->create();
        $pendingProject = Project::factory()->pending()->create();

        expect($cancelledProject->isCancelled())->toBeTrue();
        expect($pendingProject->isCancelled())->toBeFalse();
    });
});

describe('Customer with Projects', function (): void {
    it('loads projects in customer show page', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        Project::factory()->count(3)->forCustomer($customer)->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Show')
            ->has('customer.projects', 3)
        );
    });

    it('customer has projects relationship', function (): void {
        $customer = Customer::factory()->create();
        Project::factory()->count(3)->forCustomer($customer)->create();

        expect($customer->projects)->toHaveCount(3);
    });

    it('deletes projects when customer is deleted', function (): void {
        $customer = Customer::factory()->create();
        $project = Project::factory()->forCustomer($customer)->create();

        $customer->delete();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    });
});

describe('Project Status Workflow', function (): void {
    it('can update project status to in_progress', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();
        $project = Project::factory()->pending()->forCustomer($customer)->forService($service)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => $project->name,
            'service_id' => $service->id,
            'status' => 'in_progress',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'in_progress',
        ]);
    });

    it('can update project status to completed', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();
        $project = Project::factory()->inProgress()->forCustomer($customer)->forService($service)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => $project->name,
            'service_id' => $service->id,
            'status' => 'completed',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'completed',
        ]);
    });

    it('can update project status to on_hold', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();
        $project = Project::factory()->inProgress()->forCustomer($customer)->forService($service)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => $project->name,
            'service_id' => $service->id,
            'status' => 'on_hold',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'on_hold',
        ]);
    });

    it('can update project status to cancelled', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();
        $service = Service::factory()->active()->create();
        $project = Project::factory()->pending()->forCustomer($customer)->forService($service)->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}/projects/{$project->id}", [
            'name' => $project->name,
            'service_id' => $service->id,
            'status' => 'cancelled',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'cancelled',
        ]);
    });
});
