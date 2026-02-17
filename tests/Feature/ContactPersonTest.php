<?php

use App\Exceptions\InvalidContactPersonException;
use App\Models\ContactPerson;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Services\ContactPersonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Contact Person Service', function (): void {
    it('adds contact to business lead', function (): void {
        $lead = Lead::factory()->business()->create();
        $service = new ContactPersonService;

        $contact = $service->addContact($lead, [
            'name' => 'John Contact',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'designation' => 'CEO',
            'is_primary' => true,
        ]);

        expect($contact->name)->toBe('John Contact');
        expect($contact->is_primary)->toBeTrue();
        expect($lead->contactPeople)->toHaveCount(1);
    });

    it('throws exception when adding contact to individual lead', function (): void {
        $lead = Lead::factory()->individual()->create();
        $service = new ContactPersonService;

        expect(fn () => $service->addContact($lead, [
            'name' => 'John Contact',
        ]))->toThrow(InvalidContactPersonException::class);
    });

    it('sets only one primary contact', function (): void {
        $lead = Lead::factory()->business()->create();
        $service = new ContactPersonService;

        $contact1 = $service->addContact($lead, [
            'name' => 'First Contact',
            'is_primary' => true,
        ]);

        $contact2 = $service->addContact($lead, [
            'name' => 'Second Contact',
            'is_primary' => true,
        ]);

        $contact1->refresh();

        expect($contact1->is_primary)->toBeFalse();
        expect($contact2->is_primary)->toBeTrue();
    });

    it('updates contact details', function (): void {
        $lead = Lead::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'name' => 'Original Name',
        ]);

        $service = new ContactPersonService;
        $service->updateContact($contact, [
            'name' => 'Updated Name',
            'designation' => 'CFO',
        ]);

        $contact->refresh();

        expect($contact->name)->toBe('Updated Name');
        expect($contact->designation)->toBe('CFO');
    });

    it('sets contact as primary', function (): void {
        $lead = Lead::factory()->business()->create();
        $contact1 = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'is_primary' => true,
        ]);
        $contact2 = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'is_primary' => false,
        ]);

        $service = new ContactPersonService;
        $service->setPrimary($contact2);

        $contact1->refresh();
        $contact2->refresh();

        expect($contact1->is_primary)->toBeFalse();
        expect($contact2->is_primary)->toBeTrue();
    });

    it('deletes contact', function (): void {
        $lead = Lead::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
        ]);

        $service = new ContactPersonService;
        $service->deleteContact($contact);

        $this->assertDatabaseMissing('contact_people', ['id' => $contact->id]);
    });
});

describe('Contact Person Controller - Leads', function (): void {
    it('shows create form for business lead contact', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/contacts/create");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ContactPeople/Create')
            ->has('parent')
            ->where('parentType', 'lead')
        );
    });

    it('denies create form for individual lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->individual()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/contacts/create");

        $response->assertStatus(403);
    });

    it('stores contact for business lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/contacts", [
            'name' => 'New Contact',
            'email' => 'contact@example.com',
            'mobile' => '1234567890',
            'designation' => 'Manager',
            'is_primary' => true,
        ]);

        $response->assertRedirect("/leads/{$lead->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_people', [
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'name' => 'New Contact',
            'is_primary' => true,
        ]);
    });

    it('shows edit form for contact', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
        ]);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/contacts/{$contact->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ContactPeople/Edit')
            ->has('contact')
        );
    });

    it('updates contact', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($user)->put("/leads/{$lead->id}/contacts/{$contact->id}", [
            'name' => 'Updated Name',
            'designation' => 'Director',
        ]);

        $response->assertRedirect("/leads/{$lead->id}");

        $this->assertDatabaseHas('contact_people', [
            'id' => $contact->id,
            'name' => 'Updated Name',
            'designation' => 'Director',
        ]);
    });

    it('deletes contact', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
        ]);

        $response = $this->actingAs($user)->delete("/leads/{$lead->id}/contacts/{$contact->id}");

        $response->assertRedirect("/leads/{$lead->id}");

        $this->assertDatabaseMissing('contact_people', ['id' => $contact->id]);
    });

    it('sets contact as primary', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();
        $contact1 = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'is_primary' => true,
        ]);
        $contact2 = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/contacts/{$contact2->id}/set-primary");

        $response->assertRedirect("/leads/{$lead->id}");

        $contact1->refresh();
        $contact2->refresh();

        expect($contact1->is_primary)->toBeFalse();
        expect($contact2->is_primary)->toBeTrue();
    });
});

describe('Contact Person Controller - Customers', function (): void {
    it('shows create form for business customer contact', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->business()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/contacts/create");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ContactPeople/Create')
            ->has('parent')
            ->where('parentType', 'customer')
        );
    });

    it('stores contact for business customer', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->business()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/contacts", [
            'name' => 'Customer Contact',
            'email' => 'customercontact@example.com',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('contact_people', [
            'contactable_type' => Customer::class,
            'contactable_id' => $customer->id,
            'name' => 'Customer Contact',
        ]);
    });
});

describe('Contact Person Authorization', function (): void {
    it('denies access to users without manage contact persons permission', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $lead = Lead::factory()->business()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/contacts/create");

        $response->assertStatus(403);
    });

    it('allows sales role to manage contacts', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/contacts/create");

        $response->assertStatus(200);
    });
});

describe('Contact Person Validation', function (): void {
    it('validates required name field', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/contacts", [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates email format', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/contacts", [
            'name' => 'Test Contact',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    });
});
