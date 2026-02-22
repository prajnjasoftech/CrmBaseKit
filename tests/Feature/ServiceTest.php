<?php

use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Service Index', function (): void {
    it('shows services list to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Service::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/services');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Index')
            ->has('services.data', 3)
        );
    });

    it('denies access to unauthorized user', function (): void {
        $user = User::factory()->create();
        // User without any role/permission

        $response = $this->actingAs($user)->get('/services');

        $response->assertStatus(403);
    });

    it('allows sales role to view services', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->get('/services');

        $response->assertStatus(200);
    });

    it('denies access to unauthenticated user', function (): void {
        $response = $this->get('/services');

        $response->assertRedirect('/login');
    });
});

describe('Service Create', function (): void {
    it('shows create form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/services/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Create')
        );
    });

    it('stores service with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/services', [
            'name' => 'Web Development',
            'description' => 'Full-stack web development services',
            'status' => 'active',
        ]);

        $response->assertRedirect('/services');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'name' => 'Web Development',
            'description' => 'Full-stack web development services',
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    });

    it('stores service with minimal data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/services', [
            'name' => 'Consulting',
        ]);

        $response->assertRedirect('/services');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'name' => 'Consulting',
            'description' => null,
            'status' => 'active',
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/services', []);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates name max length', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/services', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates status enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/services', [
            'name' => 'Test Service',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    });

    it('denies create to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/services', [
            'name' => 'Test Service',
        ]);

        $response->assertStatus(403);
    });

    it('denies create form to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->get('/services/create');

        $response->assertStatus(403);
    });
});

describe('Service Show', function (): void {
    it('shows service details to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->get("/services/{$service->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Show')
            ->has('service')
            ->where('service.id', $service->id)
        );
    });

    it('loads creator relationship', function (): void {
        $creator = User::factory()->create(['name' => 'John Creator']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($user)->get("/services/{$service->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Show')
            ->where('service.creator.name', 'John Creator')
        );
    });
});

describe('Service Edit', function (): void {
    it('shows edit form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->get("/services/{$service->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Edit')
            ->has('service')
        );
    });

    it('updates service with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->put("/services/{$service->id}", [
            'name' => 'Updated Service',
            'description' => 'Updated description',
            'status' => 'inactive',
        ]);

        $response->assertRedirect('/services');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service',
            'description' => 'Updated description',
            'status' => 'inactive',
        ]);
    });

    it('validates required fields on update', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->put("/services/{$service->id}", [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('denies edit to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->put("/services/{$service->id}", [
            'name' => 'Updated Service',
        ]);

        $response->assertStatus(403);
    });
});

describe('Service Delete', function (): void {
    it('deletes service', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->delete("/services/{$service->id}");

        $response->assertRedirect('/services');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    });

    it('denies delete to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->delete("/services/{$service->id}");

        $response->assertStatus(403);
    });

    it('denies delete to manager role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->delete("/services/{$service->id}");

        $response->assertStatus(403);
    });
});

describe('Service Search', function (): void {
    it('filters services by name', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Service::factory()->create(['name' => 'Web Development']);
        Service::factory()->create(['name' => 'Mobile Development']);
        Service::factory()->create(['name' => 'Consulting']);

        $response = $this->actingAs($user)->get('/services?search=Development');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Index')
            ->has('services.data', 2)
            ->has('filters')
            ->where('filters.search', 'Development')
        );
    });

    it('filters services by description', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Service::factory()->create(['name' => 'Service A', 'description' => 'Full-stack web development']);
        Service::factory()->create(['name' => 'Service B', 'description' => 'Mobile app development']);

        $response = $this->actingAs($user)->get('/services?search=Full-stack');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Index')
            ->has('services.data', 1)
        );
    });

    it('returns all services when search is empty', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Service::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/services?search=');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Index')
            ->has('services.data', 5)
        );
    });
});

describe('Service Authorization', function (): void {
    it('allows admin full access', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = Service::factory()->create();

        $this->actingAs($user)->get('/services')->assertStatus(200);
        $this->actingAs($user)->get('/services/create')->assertStatus(200);
        $this->actingAs($user)->get("/services/{$service->id}")->assertStatus(200);
        $this->actingAs($user)->get("/services/{$service->id}/edit")->assertStatus(200);
    });

    it('allows manager to view, create, and edit but not delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $service = Service::factory()->create();

        $this->actingAs($user)->get('/services')->assertStatus(200);
        $this->actingAs($user)->get('/services/create')->assertStatus(200);
        $this->actingAs($user)->get("/services/{$service->id}/edit")->assertStatus(200);
        $this->actingAs($user)->delete("/services/{$service->id}")->assertStatus(403);
    });

    it('allows sales view only', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service = Service::factory()->create();

        $this->actingAs($user)->get('/services')->assertStatus(200);
        $this->actingAs($user)->get("/services/{$service->id}")->assertStatus(200);
        $this->actingAs($user)->get('/services/create')->assertStatus(403);
        $this->actingAs($user)->get("/services/{$service->id}/edit")->assertStatus(403);
    });

    it('denies user role all access', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $service = Service::factory()->create();

        $this->actingAs($user)->get('/services')->assertStatus(403);
        $this->actingAs($user)->get("/services/{$service->id}")->assertStatus(403);
    });
});

describe('Service Model', function (): void {
    it('has creator relationship', function (): void {
        $creator = User::factory()->create();
        $service = Service::factory()->create(['created_by' => $creator->id]);

        expect($service->creator->id)->toBe($creator->id);
    });

    it('has leads relationship', function (): void {
        $service = Service::factory()->create();
        Lead::factory()->count(3)->create(['service_id' => $service->id]);

        expect($service->leads)->toHaveCount(3);
    });

    it('uses soft deletes', function (): void {
        $service = Service::factory()->create();
        $service->delete();

        $this->assertSoftDeleted('services', ['id' => $service->id]);
        expect(Service::withTrashed()->find($service->id))->not->toBeNull();
    });
});

describe('Lead with Service', function (): void {
    it('shows services in lead create form', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Service::factory()->count(3)->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/leads/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Create')
            ->has('services', 3)
        );
    });

    it('only shows active services in lead create form', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Service::factory()->count(2)->create(['status' => 'active']);
        Service::factory()->count(1)->create(['status' => 'inactive']);

        $response = $this->actingAs($user)->get('/leads/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Create')
            ->has('services', 2)
        );
    });

    it('stores lead with service', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service = Service::factory()->create();

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'John Doe',
            'entity_type' => 'individual',
            'source' => 'website',
            'status' => 'new',
            'service_id' => $service->id,
        ]);

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'service_id' => $service->id,
        ]);
    });

    it('validates service_id exists', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'John Doe',
            'entity_type' => 'individual',
            'source' => 'website',
            'service_id' => 999,
        ]);

        $response->assertSessionHasErrors(['service_id']);
    });

    it('allows null service_id', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'John Doe',
            'entity_type' => 'individual',
            'source' => 'website',
            'service_id' => '',
        ]);

        $response->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'service_id' => null,
        ]);
    });

    it('shows services in lead edit form', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        Service::factory()->count(2)->create(['status' => 'active']);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Edit')
            ->has('services', 2)
        );
    });

    it('updates lead service', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();
        $lead = Lead::factory()->create(['service_id' => $service1->id]);

        $response = $this->actingAs($user)->put("/leads/{$lead->id}", [
            'name' => $lead->name,
            'source' => $lead->source,
            'status' => $lead->status,
            'service_id' => $service2->id,
        ]);

        $response->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'service_id' => $service2->id,
        ]);
    });

    it('loads service relationship in lead show', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service = Service::factory()->create(['name' => 'Web Development']);
        $lead = Lead::factory()->create(['service_id' => $service->id]);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Show')
            ->has('lead.service')
            ->where('lead.service.name', 'Web Development')
        );
    });

    it('loads service relationship in lead index', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $service = Service::factory()->create(['name' => 'Consulting']);
        Lead::factory()->create(['service_id' => $service->id]);

        $response = $this->actingAs($user)->get('/leads');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Index')
            ->has('leads.data.0.service')
        );
    });
});

describe('Lead Model Service Relationship', function (): void {
    it('has service relationship', function (): void {
        $service = Service::factory()->create();
        $lead = Lead::factory()->create(['service_id' => $service->id]);

        expect($lead->service->id)->toBe($service->id);
    });

    it('returns null when no service assigned', function (): void {
        $lead = Lead::factory()->create(['service_id' => null]);

        expect($lead->service)->toBeNull();
    });

    it('nullifies service_id when service is deleted', function (): void {
        $service = Service::factory()->create();
        $lead = Lead::factory()->create(['service_id' => $service->id]);

        $service->forceDelete();

        $lead->refresh();
        expect($lead->service_id)->toBeNull();
    });
});
