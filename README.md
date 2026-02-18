# CRM Base Kit

A Laravel-based CRM starter kit with Leads & Customers management, built with Inertia.js and React.

## Features

### Core Modules

- **Leads Management** - Track potential customers through the sales pipeline
- **Customers Management** - Manage converted customers and their lifecycle
- **Follow-ups** - Schedule and track follow-up activities for leads and customers
- **Contact Persons** - Multiple contacts per business entity
- **Users & Roles** - Role-based access control with granular permissions
- **Businesses** - Multi-tenant business management

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

### Customer Lifecycle

Customer statuses:
- `active` - Active customer
- `inactive` - Temporarily inactive
- `churned` - Customer has left

### Data Immutability

To maintain data integrity, certain fields become immutable after creation:
- **Email** - Cannot be changed once set (can be set if originally null)
- **Phone** - Cannot be changed once set (can be set if originally null)

This ensures contact information remains consistent for audit trails and historical records.

### Follow-ups

Schedule and track follow-up activities for leads and customers:
- **Status tracking**: pending, completed, cancelled
- **Date scheduling**: Set follow-up dates with overdue detection
- **Notes**: Add context and details for each follow-up
- **Dashboard integration**: View upcoming and overdue follow-ups at a glance
- **Polymorphic**: Works with both leads and customers
- **Cascade delete**: Follow-ups are automatically deleted with their parent entity

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

### Super Admin
- Full access to all features
- Can manage users and roles
- Can delete any record

### Admin
- Manage users, businesses, leads, customers
- Cannot delete users

### Manager
- View and manage leads and customers
- View users and businesses
- Cannot delete records

### Sales
- Create and edit leads and customers
- Manage contact persons
- Cannot delete records

### User
- View-only access to leads and customers

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

### Contact Persons
- `GET /leads/{lead}/contacts/create` - Create contact for lead
- `POST /leads/{lead}/contacts` - Store contact for lead
- `GET /customers/{customer}/contacts/create` - Create contact for customer
- `POST /customers/{customer}/contacts` - Store contact for customer
- `GET /contacts/{contact}/edit` - Edit contact
- `PUT /contacts/{contact}` - Update contact
- `DELETE /contacts/{contact}` - Delete contact
- `POST /contacts/{contact}/set-primary` - Set as primary contact

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/LeadTest.php

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
│   │   └── ContactPersonController.php
│   └── Requests/
│       ├── StoreFollowUpRequest.php
│       ├── UpdateFollowUpRequest.php
│       ├── StoreLeadRequest.php
│       ├── UpdateLeadRequest.php
│       ├── StoreCustomerRequest.php
│       ├── UpdateCustomerRequest.php
│       ├── StoreContactPersonRequest.php
│       └── UpdateContactPersonRequest.php
├── Models/
│   ├── FollowUp.php
│   ├── Lead.php
│   ├── Customer.php
│   └── ContactPerson.php
├── Policies/
│   ├── FollowUpPolicy.php
│   ├── LeadPolicy.php
│   ├── CustomerPolicy.php
│   └── ContactPersonPolicy.php
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
    ├── Dashboard.jsx             # Shows upcoming/overdue follow-ups
    ├── FollowUps/
    │   ├── Create.jsx
    │   └── Edit.jsx
    ├── Leads/
    │   ├── Index.jsx
    │   ├── Create.jsx
    │   ├── Show.jsx              # Includes follow-up management
    │   ├── Edit.jsx
    │   └── Convert.jsx
    ├── Customers/
    │   ├── Index.jsx
    │   ├── Create.jsx
    │   ├── Show.jsx              # Includes follow-up management
    │   └── Edit.jsx
    └── ContactPeople/
        ├── Create.jsx
        └── Edit.jsx
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
