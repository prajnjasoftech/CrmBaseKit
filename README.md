# CRM Base Kit

A Laravel-based CRM starter kit with Leads & Customers management, built with Inertia.js and React.

## Features

### Core Modules

- **Leads Management** - Track potential customers through the sales pipeline
- **Customers Management** - Manage converted customers and their lifecycle
- **Projects** - Manage customer projects with service association and status tracking
- **Services** - Manage service offerings that can be associated with leads and projects
- **Follow-ups** - Schedule and track follow-up activities for leads and customers
- **Contact Persons** - Multiple contacts per business entity
- **Users & Roles** - Role-based access control with granular permissions
- **Roles Management** - CRUD for roles with grouped permission assignment
- **Businesses** - Multi-tenant business management

### Permission-Based UI

All pages implement permission-based UI visibility:
- Buttons and actions are shown/hidden based on user permissions
- Frontend receives permissions via Inertia shared data
- Each page uses `can('permission name')` helper for conditional rendering
- Prevents UI clutter by hiding unavailable actions

### Entity Types

Both Leads and Customers support two entity types:

- **Individual** - Personal contacts with first name, last name, email, and phone
- **Business** - Company entities with company name and multiple contact persons

### Search & Filtering

Both Leads and Customers support search functionality:
- Search by first name, last name, company name
- Search by email or phone
- Results are paginated

### Lead Lifecycle

Leads progress through the following statuses:
- `new` - Newly created lead
- `contacted` - Initial contact made
- `qualified` - Lead qualified for sales
- `proposal` - Proposal sent
- `negotiation` - In negotiation
- `won` - Deal closed successfully
- `lost` - Deal lost

Lead sources tracked:
- `website`, `referral`, `social_media`, `advertisement`, `cold_call`, `trade_show`, `other`

### Lead to Customer Conversion

When a lead reaches "won" status, it can be converted to a customer:
- All lead data is copied to the new customer record
- Contact persons are transferred for business entities
- Original lead is marked as converted with reference to the customer
- Converted leads cannot be modified or re-converted

### Services

Manage service offerings that can be associated with leads:
- **Service catalog**: Create and manage available services
- **Lead association**: Associate services with leads during creation or editing
- **Status management**: Mark services as active or inactive
- **Soft delete**: Services are soft-deleted to preserve historical data

Service fields:
- `name` - Service name (required)
- `description` - Detailed description (optional)
- `status` - active/inactive

### Customer Lifecycle

Customer statuses:
- `active` - Active customer
- `inactive` - Temporarily inactive
- `churned` - Customer has left

### Projects

Customers can have multiple projects with service association:
- **Service linkage**: Each project is associated with a service offering
- **Status tracking**: Track project progress through its lifecycle
- **Date management**: Set start and end dates for project planning
- **Budget tracking**: Record project budget
- **Assignment**: Assign projects to team members
- **Soft delete**: Projects are soft-deleted to preserve historical data
- **Cascade delete**: Projects are automatically deleted with their parent customer

Project statuses:
- `pending` - Project not yet started
- `in_progress` - Project is actively being worked on
- `on_hold` - Project temporarily paused
- `completed` - Project successfully finished
- `cancelled` - Project was cancelled

Project fields:
- `name` - Project name (required)
- `description` - Detailed description (optional)
- `service_id` - Associated service (required)
- `status` - Project status
- `start_date` - Project start date (optional)
- `end_date` - Project end date (optional)
- `budget` - Project budget (optional)
- `assigned_to` - Assigned team member (optional)

### Follow-ups

Schedule and track follow-up activities for leads and customers:
- **Status tracking**: pending, completed, cancelled
- **Date scheduling**: Set follow-up dates with overdue detection
- **Notes**: Add context and details for each follow-up
- **Dashboard integration**: View upcoming and overdue follow-ups at a glance
- **Polymorphic**: Works with both leads and customers
- **Cascade delete**: Follow-ups are automatically deleted with their parent entity
- **Role-based filtering**:
  - Managers/Admins see all follow-ups across the system
  - Sales/Users see only follow-ups for leads/customers assigned to them

### Contact Persons

Business entities (leads and customers) can have multiple contact persons:
- Each contact has: name, email, phone, position, notes
- One contact can be marked as **primary**
- Contacts are automatically transferred during lead conversion
- Contacts are cascade-deleted when the parent entity is deleted

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: React 18, Inertia.js
- **Styling**: Tailwind CSS
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Laravel Permission

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd CRMBaseKit

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations and seed data
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## Default Users

After seeding, the following users are available:

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password | Super Admin |
| manager@example.com | password | Manager |
| sales@example.com | password | Sales |
| user@example.com | password | User |

## Roles & Permissions

### Roles Management

Administrators can manage roles through the Roles CRUD interface:
- **Create roles**: Define new roles with custom permissions
- **Edit roles**: Modify role permissions (built-in roles like super-admin protected)
- **Delete roles**: Remove roles (with protection for system roles)
- **Grouped permissions**: Permissions organized by module (Users, Leads, Customers, etc.)
- **Visual selection**: Checkbox-based permission assignment

### Default Roles

#### Super Admin
- Full access to all features
- Can manage users and roles
- Can delete any record

#### Admin
- Manage users, businesses, leads, customers, services
- Cannot delete users

#### Manager
- View and manage leads, customers, and services
- View users and businesses
- Cannot delete records
- See all follow-ups on dashboard

#### Sales
- Create and edit leads and customers
- View services (for lead association)
- Manage contact persons and follow-ups
- Cannot delete records
- See only follow-ups for assigned leads/customers on dashboard

#### User
- View-only access to leads and customers
- See only follow-ups for assigned leads/customers on dashboard

### Permission Groups

Permissions are organized into groups:
- **Users**: view, create, edit, delete users
- **Roles**: view, create, edit, delete roles
- **Leads**: view, create, edit, delete, convert leads
- **Customers**: view, create, edit, delete customers
- **Projects**: view, create, edit, delete projects
- **Businesses**: view, create, edit, delete businesses
- **Services**: view, create, edit, delete services
- **Follow-ups**: manage follow-ups

## API Routes

### Leads
- `GET /leads` - List all leads
- `GET /leads/create` - Show create form
- `POST /leads` - Store new lead
- `GET /leads/{lead}` - Show lead details
- `GET /leads/{lead}/edit` - Show edit form
- `PUT /leads/{lead}` - Update lead
- `DELETE /leads/{lead}` - Delete lead
- `GET /leads/{lead}/convert` - Show conversion form
- `POST /leads/{lead}/convert` - Convert to customer

### Customers
- `GET /customers` - List all customers
- `GET /customers/create` - Show create form
- `POST /customers` - Store new customer
- `GET /customers/{customer}` - Show customer details
- `GET /customers/{customer}/edit` - Show edit form
- `PUT /customers/{customer}` - Update customer
- `DELETE /customers/{customer}` - Delete customer

### Services
- `GET /services` - List all services
- `GET /services/create` - Show create form
- `POST /services` - Store new service
- `GET /services/{service}` - Show service details
- `GET /services/{service}/edit` - Show edit form
- `PUT /services/{service}` - Update service
- `DELETE /services/{service}` - Delete service

### Follow-ups (Leads)
- `GET /leads/{lead}/follow-ups/create` - Show create form
- `POST /leads/{lead}/follow-ups` - Store new follow-up
- `GET /leads/{lead}/follow-ups/{followUp}/edit` - Show edit form
- `PUT /leads/{lead}/follow-ups/{followUp}` - Update follow-up
- `DELETE /leads/{lead}/follow-ups/{followUp}` - Delete follow-up
- `POST /leads/{lead}/follow-ups/{followUp}/complete` - Mark as completed

### Follow-ups (Customers)
- `GET /customers/{customer}/follow-ups/create` - Show create form
- `POST /customers/{customer}/follow-ups` - Store new follow-up
- `GET /customers/{customer}/follow-ups/{followUp}/edit` - Show edit form
- `PUT /customers/{customer}/follow-ups/{followUp}` - Update follow-up
- `DELETE /customers/{customer}/follow-ups/{followUp}` - Delete follow-up
- `POST /customers/{customer}/follow-ups/{followUp}/complete` - Mark as completed

### Projects (Customers)
- `GET /customers/{customer}/projects/create` - Show create form
- `POST /customers/{customer}/projects` - Store new project
- `GET /customers/{customer}/projects/{project}` - Show project details
- `GET /customers/{customer}/projects/{project}/edit` - Show edit form
- `PUT /customers/{customer}/projects/{project}` - Update project
- `DELETE /customers/{customer}/projects/{project}` - Delete project

### Contact Persons
- `GET /leads/{lead}/contacts/create` - Create contact for lead
- `POST /leads/{lead}/contacts` - Store contact for lead
- `GET /customers/{customer}/contacts/create` - Create contact for customer
- `POST /customers/{customer}/contacts` - Store contact for customer
- `GET /contacts/{contact}/edit` - Edit contact
- `PUT /contacts/{contact}` - Update contact
- `DELETE /contacts/{contact}` - Delete contact
- `POST /contacts/{contact}/set-primary` - Set as primary contact

### Roles
- `GET /roles` - List all roles
- `GET /roles/create` - Show create form
- `POST /roles` - Store new role
- `GET /roles/{role}` - Show role details
- `GET /roles/{role}/edit` - Show edit form
- `PUT /roles/{role}` - Update role
- `DELETE /roles/{role}` - Delete role

## Testing

```bash
# Run all tests
php artisan test

# Run with Pest parallel execution (faster)
vendor/bin/pest --parallel

# Run specific test file
php artisan test tests/Feature/LeadTest.php
php artisan test tests/Feature/ServiceTest.php
php artisan test tests/Feature/ProjectTest.php

# Run with coverage
php artisan test --coverage
```

## Project Structure

```
app/
├── Enums/
│   ├── EntityType.php          # Individual/Business enum
│   ├── LeadStatus.php          # Lead status enum
│   ├── LeadSource.php          # Lead source enum
│   └── CustomerStatus.php      # Customer status enum
├── Exceptions/
│   └── ImmutableFieldException.php
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── FollowUpController.php
│   │   ├── LeadController.php
│   │   ├── CustomerController.php
│   │   ├── ContactPersonController.php
│   │   ├── ServiceController.php
│   │   ├── ProjectController.php
│   │   └── RoleController.php
│   └── Requests/
│       ├── StoreFollowUpRequest.php
│       ├── UpdateFollowUpRequest.php
│       ├── StoreLeadRequest.php
│       ├── UpdateLeadRequest.php
│       ├── StoreCustomerRequest.php
│       ├── UpdateCustomerRequest.php
│       ├── StoreServiceRequest.php
│       ├── UpdateServiceRequest.php
│       ├── StoreProjectRequest.php
│       ├── UpdateProjectRequest.php
│       ├── StoreContactPersonRequest.php
│       └── UpdateContactPersonRequest.php
├── Models/
│   ├── FollowUp.php
│   ├── Lead.php
│   ├── Customer.php
│   ├── Service.php
│   ├── Project.php
│   └── ContactPerson.php
├── Policies/
│   ├── FollowUpPolicy.php
│   ├── LeadPolicy.php
│   ├── CustomerPolicy.php
│   ├── ServicePolicy.php
│   ├── ProjectPolicy.php
│   ├── ContactPersonPolicy.php
│   └── RolePolicy.php
└── Services/
    ├── FollowUpService.php
    └── ContactPersonService.php

resources/js/
├── Components/
│   ├── FollowUpForm.jsx
│   ├── FollowUpList.jsx
│   ├── ContactPersonForm.jsx
│   └── ContactPersonList.jsx
└── Pages/
    ├── Dashboard.jsx             # Shows role-filtered follow-ups
    ├── FollowUps/
    │   ├── Create.jsx
    │   └── Edit.jsx
    ├── Leads/
    │   ├── Index.jsx             # Permission-based UI
    │   ├── Create.jsx            # Includes service selection
    │   ├── Show.jsx              # Includes follow-up management, displays service
    │   ├── Edit.jsx              # Includes service selection
    │   └── Convert.jsx
    ├── Customers/
    │   ├── Index.jsx             # Permission-based UI
    │   ├── Create.jsx
    │   ├── Show.jsx              # Includes follow-up management, projects
    │   └── Edit.jsx
    ├── Projects/
    │   ├── Create.jsx            # Create project for customer
    │   ├── Show.jsx              # Project details
    │   └── Edit.jsx              # Edit project
    ├── Services/
    │   ├── Index.jsx             # List with search/pagination
    │   ├── Create.jsx            # Create service form
    │   ├── Show.jsx              # Service details
    │   └── Edit.jsx              # Edit service form
    ├── Roles/
    │   ├── Index.jsx             # List roles with permissions
    │   ├── Create.jsx            # Create role with grouped permissions
    │   ├── Show.jsx              # View role details
    │   └── Edit.jsx              # Edit role permissions
    └── ContactPeople/
        ├── Create.jsx
        └── Edit.jsx
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
