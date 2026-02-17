# CRM Base Kit - Architecture

## Overview

CRM Base Kit is a production-grade starter kit built with Laravel 12, React 19, and Inertia.js. It provides a solid foundation for building CRM applications with modern best practices.

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12.x |
| Frontend | React 19 + Inertia.js 2.0 |
| Database | MySQL 8.x |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel Permission |
| Styling | Bootstrap 5.3 + SCSS |
| Build Tool | Vite |
| Testing | Pest PHP v3.8 |
| Static Analysis | Larastan (PHPStan) Level 9 (max) |
| Code Style | Laravel Pint |
| Type Safety | `declare(strict_types=1)` on all PHP files |

## Modules Overview

### 1. Authentication Module
- Login with rate limiting (5 attempts)
- Registration with automatic role assignment
- Password reset via email
- Email verification
- Session-based authentication with remember me

### 2. User Management Module
- Full CRUD operations
- Role assignment (super-admin, admin, manager, sales, user)
- Self-profile editing
- Password change functionality

### 3. Business Registration Module
- Business entity management
- Multiple status tracking (active, inactive, pending)
- Industry categorization
- Contact information and address management
- Soft deletes for data retention

### 4. Leads Module
- Full CRUD for sales leads
- Pipeline status tracking (new, contacted, qualified, proposal, negotiation, won, lost)
- Lead source tracking (website, referral, advertisement, cold call, social media, other)
- User assignment for sales ownership
- Business association
- Lead-to-Customer conversion workflow
- Soft deletes for data retention

### 5. Customers Module
- Full CRUD for customers
- Status management (active, inactive, churned)
- Full address management
- Conversion tracking from leads
- User assignment for account management
- Business association
- Soft deletes for data retention

### 6. RBAC (Role-Based Access Control)
5 predefined roles with granular permissions:

| Role | Permissions |
|------|-------------|
| super-admin | Full system access |
| admin | Manage users, businesses, leads, customers, news |
| manager | View users, manage businesses, leads, customers |
| sales | Manage leads and customers only |
| user | View-only access to leads, customers, news |

## Directory Structure

```
CRMBaseKit/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Authentication controllers
│   │   │   ├── BusinessController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── LeadController.php
│   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/
│   │       ├── Auth/              # Auth form requests
│   │       ├── ConvertLeadRequest.php
│   │       ├── StoreBusinessRequest.php
│   │       ├── StoreCustomerRequest.php
│   │       ├── StoreLeadRequest.php
│   │       ├── StoreUserRequest.php
│   │       ├── UpdateBusinessRequest.php
│   │       ├── UpdateCustomerRequest.php
│   │       ├── UpdateLeadRequest.php
│   │       └── UpdateUserRequest.php
│   ├── Models/
│   │   ├── Business.php
│   │   ├── Customer.php
│   │   ├── Lead.php
│   │   └── User.php
│   └── Policies/
│       ├── BusinessPolicy.php
│       ├── CustomerPolicy.php
│       ├── LeadPolicy.php
│       └── UserPolicy.php
├── database/
│   ├── factories/
│   │   ├── BusinessFactory.php
│   │   ├── CustomerFactory.php
│   │   ├── LeadFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   └── seeders/
│       └── RolesAndPermissionsSeeder.php
├── resources/
│   ├── css/
│   │   ├── admin/
│   │   │   ├── _layout.scss
│   │   │   ├── _sidebar.scss
│   │   │   ├── _header.scss
│   │   │   ├── _footer.scss
│   │   │   ├── _components.scss
│   │   │   └── _auth.scss
│   │   ├── _variables.scss
│   │   └── app.scss
│   └── js/
│       ├── Components/
│       │   ├── Sidebar.jsx
│       │   ├── Header.jsx
│       │   └── Footer.jsx
│       ├── Layouts/
│       │   └── AdminLayout.jsx
│       └── Pages/
│           ├── Auth/
│           │   ├── Login.jsx
│           │   ├── Register.jsx
│           │   ├── ForgotPassword.jsx
│           │   ├── ResetPassword.jsx
│           │   └── VerifyEmail.jsx
│           ├── Businesses/
│           │   ├── Index.jsx
│           │   ├── Create.jsx
│           │   ├── Edit.jsx
│           │   └── Show.jsx
│           ├── Customers/
│           │   ├── Index.jsx
│           │   ├── Create.jsx
│           │   ├── Edit.jsx
│           │   └── Show.jsx
│           ├── Leads/
│           │   ├── Index.jsx
│           │   ├── Create.jsx
│           │   ├── Edit.jsx
│           │   ├── Show.jsx
│           │   └── Convert.jsx
│           ├── Users/
│           │   ├── Index.jsx
│           │   ├── Create.jsx
│           │   ├── Edit.jsx
│           │   └── Show.jsx
│           └── Dashboard.jsx
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        ├── AuthTest.php
        ├── BusinessTest.php
        ├── CustomerTest.php
        ├── LeadTest.php
        └── UserTest.php
```

## Design Patterns

### Form Request Validation
All input validation is handled in Form Request classes with authorization checks.

```php
class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create businesses') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'unique:businesses'],
        ];
    }
}
```

### Policy-Based Authorization
Authorization is handled through Laravel Policies with explicit `$this->authorize()` calls in controllers.

```php
class BusinessController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Business::class);
        // ...
    }
}
```

```php
class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view businesses');
    }
}
```

### Inertia Shared Data
Common data is shared via HandleInertiaRequests middleware.

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->display_role,
            ] : null,
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ];
}
```

## Frontend Architecture

### Component Structure
- **Layouts**: Page wrappers (AdminLayout)
- **Components**: Reusable UI elements (Sidebar, Header, Footer)
- **Pages**: Route-specific components organized by module

### Inertia.js Flow
1. Request hits Laravel route
2. Controller returns `Inertia::render('PageName', $props)`
3. Inertia loads React component with props
4. SPA navigation preserves state

### Form Handling with useForm
```jsx
const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
});

const handleSubmit = (e) => {
    e.preventDefault();
    post('/businesses');
};
```

## Database Design

### Core Tables
| Table | Description |
|-------|-------------|
| users | System users with authentication |
| businesses | Registered business entities |
| leads | Sales leads with pipeline status |
| customers | Converted leads and direct customers |
| roles | Spatie Permission roles |
| permissions | Spatie Permission permissions |
| model_has_roles | Role-user pivot |
| model_has_permissions | Permission-user pivot |
| role_has_permissions | Permission-role pivot |
| password_reset_tokens | Password reset tracking |
| sessions | Session management |

### Lead Status Flow
```
new → contacted → qualified → proposal → negotiation → won/lost
                                                        ↓
                                                   (convert)
                                                        ↓
                                                   customer
```

### Conventions
- Snake_case table names
- String length limited to 191 characters (MySQL compatibility)
- Soft deletes on business entities
- Timestamps on all tables
- Foreign key constraints

## Enterprise Code Quality

### Strict Types
All PHP files use `declare(strict_types=1)` for maximum type safety.

### Larastan Level 9
Static analysis at maximum level ensures:
- No undefined variables or properties
- Correct method signatures
- Type-safe collections and arrays
- Proper null handling

### Type-Safe Validated Data
Form requests use PHPDoc type annotations for validated data:

```php
/** @var array{name: string, email: string, password: string} $validated */
$validated = $request->validated();

$user = User::create([
    'name' => $validated['name'],
    'email' => $validated['email'],
    'password' => Hash::make($validated['password']),
]);
```

### Model Relationship Types
All model relationships include proper generics:

```php
/**
 * @return BelongsTo<User, $this>
 */
public function assignee(): BelongsTo
{
    return $this->belongsTo(User::class, 'assigned_to');
}
```

## Security Considerations

- CSRF protection via Laravel
- Rate limiting on login (5 attempts per minute)
- Password hashing with bcrypt
- XSS prevention through React's automatic escaping
- SQL injection prevention via Eloquent
- Role-based access control (RBAC)
- Session invalidation on logout
- Email verification support

## Testing Strategy

- **Feature Tests**: Test complete request/response cycles
- **Authorization Tests**: Verify role-based access
- **Validation Tests**: Ensure proper input validation
- **All tests use RefreshDatabase trait**

Run tests:
```bash
php artisan test
```

Run with coverage:
```bash
php artisan test --coverage
```
