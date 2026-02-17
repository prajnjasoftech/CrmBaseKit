# CRM Base Kit - API Documentation

## Authentication Routes

All authentication routes use session-based authentication with CSRF protection.

### Login

**POST** `/login`

Authenticates a user and creates a session.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password",
    "remember": true
}
```

**Responses:**
- `302` - Redirect to dashboard on success
- `422` - Validation errors
- `429` - Too many attempts (rate limited)

**Rate Limiting:** 5 attempts per minute per IP/email combination.

---

### Register

**POST** `/register`

Creates a new user account with 'user' role.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
}
```

**Responses:**
- `302` - Redirect to dashboard on success
- `422` - Validation errors

**Events Dispatched:** `Registered` (triggers email verification)

---

### Logout

**POST** `/logout`

Logs out the authenticated user.

**Responses:**
- `302` - Redirect to login page

---

### Forgot Password

**POST** `/forgot-password`

Sends a password reset link to the email.

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Responses:**
- `302` - Back with status message
- `422` - Email not found

---

### Reset Password

**POST** `/reset-password`

Resets the user's password using the token.

**Request Body:**
```json
{
    "token": "reset-token-from-email",
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}
```

**Responses:**
- `302` - Redirect to login on success
- `422` - Invalid token or validation errors

---

### Email Verification

**GET** `/email/verify/{id}/{hash}`

Verifies the user's email address (signed URL from email).

**POST** `/email/verification-notification`

Resends the verification email.

**Rate Limiting:** 6 requests per minute.

---

## User Management Routes

All routes require authentication and appropriate permissions.

### List Users

**GET** `/users`

Returns paginated list of users with roles.

**Permission Required:** `view users`

**Response Props:**
```json
{
    "users": {
        "data": [
            {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "roles": [{"id": 1, "name": "admin"}],
                "created_at": "2024-01-15T10:30:00Z"
            }
        ],
        "links": [...],
        "meta": {...}
    }
}
```

---

### Create User Form

**GET** `/users/create`

Returns the create user form with available roles.

**Permission Required:** `create users`

**Response Props:**
```json
{
    "roles": [
        {"id": 1, "name": "super-admin"},
        {"id": 2, "name": "admin"},
        ...
    ]
}
```

---

### Store User

**POST** `/users`

Creates a new user.

**Permission Required:** `create users`

**Request Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "manager"
}
```

**Responses:**
- `302` - Redirect to users index on success
- `422` - Validation errors

---

### Show User

**GET** `/users/{id}`

Returns user details.

**Permission Required:** `view users` OR own profile

**Response Props:**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2024-01-15T10:30:00Z",
        "roles": [{"id": 1, "name": "admin"}],
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    }
}
```

---

### Edit User Form

**GET** `/users/{id}/edit`

Returns the edit user form.

**Permission Required:** `edit users` OR own profile

**Response Props:**
```json
{
    "user": {...},
    "roles": [...]
}
```

---

### Update User

**PUT** `/users/{id}`

Updates user information.

**Permission Required:** `edit users` OR own profile

**Request Body:**
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!",
    "role": "admin"
}
```

Note: Password fields are optional. Leave blank to keep current password.

---

### Delete User

**DELETE** `/users/{id}`

Soft-deletes a user.

**Permission Required:** `delete users`

**Restrictions:** Cannot delete self.

---

## Business Management Routes

All routes require authentication and appropriate permissions.

### List Businesses

**GET** `/businesses`

Returns paginated list of businesses.

**Permission Required:** `view businesses`

**Response Props:**
```json
{
    "businesses": {
        "data": [
            {
                "id": 1,
                "name": "Acme Corp",
                "email": "contact@acme.com",
                "status": "active",
                "industry": "Technology",
                "creator": {"id": 1, "name": "John Doe"},
                "created_at": "2024-01-15T10:30:00Z"
            }
        ],
        "links": [...],
        "meta": {...}
    }
}
```

---

### Create Business Form

**GET** `/businesses/create`

Returns the create business form with options.

**Permission Required:** `create businesses`

**Response Props:**
```json
{
    "industries": ["Technology", "Healthcare", "Finance", ...],
    "countries": {"US": "United States", "UK": "United Kingdom", ...}
}
```

---

### Store Business

**POST** `/businesses`

Creates a new business.

**Permission Required:** `create businesses`

**Request Body:**
```json
{
    "name": "Acme Corp",
    "registration_number": "REG-12345",
    "email": "contact@acme.com",
    "phone": "+1-555-0100",
    "website": "https://acme.com",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "postal_code": "10001",
    "country": "US",
    "industry": "Technology",
    "status": "active"
}
```

**Required Fields:** `name`, `email`

---

### Show Business

**GET** `/businesses/{id}`

Returns business details.

**Permission Required:** `view businesses`

**Response Props:**
```json
{
    "business": {
        "id": 1,
        "name": "Acme Corp",
        "registration_number": "REG-12345",
        "email": "contact@acme.com",
        "phone": "+1-555-0100",
        "website": "https://acme.com",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "postal_code": "10001",
        "country": "US",
        "industry": "Technology",
        "status": "active",
        "creator": {"id": 1, "name": "John Doe"},
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    }
}
```

---

### Edit Business Form

**GET** `/businesses/{id}/edit`

Returns the edit business form.

**Permission Required:** `edit businesses`

**Response Props:**
```json
{
    "business": {...},
    "industries": [...],
    "countries": {...}
}
```

---

### Update Business

**PUT** `/businesses/{id}`

Updates business information.

**Permission Required:** `edit businesses`

**Request Body:** Same as Store Business

---

### Delete Business

**DELETE** `/businesses/{id}`

Soft-deletes a business.

**Permission Required:** `delete businesses`

---

## Lead Management Routes

All routes require authentication and appropriate permissions.

### List Leads

**GET** `/leads`

Returns paginated list of leads.

**Permission Required:** `view leads`

**Response Props:**
```json
{
    "leads": {
        "data": [
            {
                "id": 1,
                "name": "John Prospect",
                "email": "john@prospect.com",
                "phone": "+1-555-0100",
                "company": "Prospect Inc",
                "source": "website",
                "status": "qualified",
                "assignee": {"id": 1, "name": "Sales Rep"},
                "business": {"id": 1, "name": "Acme Corp"},
                "created_at": "2024-01-15T10:30:00Z"
            }
        ],
        "links": [...],
        "meta": {...}
    },
    "statuses": {
        "new": "New",
        "contacted": "Contacted",
        "qualified": "Qualified",
        "proposal": "Proposal",
        "negotiation": "Negotiation",
        "won": "Won",
        "lost": "Lost"
    },
    "sources": {
        "website": "Website",
        "referral": "Referral",
        "advertisement": "Advertisement",
        "cold_call": "Cold Call",
        "social_media": "Social Media",
        "other": "Other"
    }
}
```

---

### Create Lead Form

**GET** `/leads/create`

Returns the create lead form with options.

**Permission Required:** `create leads`

**Response Props:**
```json
{
    "statuses": {...},
    "sources": {...},
    "users": [{"id": 1, "name": "Sales Rep"}, ...],
    "businesses": [{"id": 1, "name": "Acme Corp"}, ...]
}
```

---

### Store Lead

**POST** `/leads`

Creates a new lead.

**Permission Required:** `create leads`

**Request Body:**
```json
{
    "name": "John Prospect",
    "email": "john@prospect.com",
    "phone": "+1-555-0100",
    "company": "Prospect Inc",
    "source": "website",
    "status": "new",
    "notes": "Interested in enterprise plan",
    "assigned_to": 1,
    "business_id": 1
}
```

**Required Fields:** `name`, `source`

---

### Show Lead

**GET** `/leads/{id}`

Returns lead details with relationships.

**Permission Required:** `view leads`

**Response Props:**
```json
{
    "lead": {
        "id": 1,
        "name": "John Prospect",
        "email": "john@prospect.com",
        "phone": "+1-555-0100",
        "company": "Prospect Inc",
        "source": "website",
        "status": "qualified",
        "notes": "Interested in enterprise plan",
        "assignee": {"id": 1, "name": "Sales Rep"},
        "business": {"id": 1, "name": "Acme Corp"},
        "customer": null,
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    },
    "statuses": {...},
    "sources": {...}
}
```

---

### Edit Lead Form

**GET** `/leads/{id}/edit`

Returns the edit lead form.

**Permission Required:** `edit leads`

---

### Update Lead

**PUT** `/leads/{id}`

Updates lead information.

**Permission Required:** `edit leads`

**Request Body:** Same as Store Lead with `status` required.

---

### Delete Lead

**DELETE** `/leads/{id}`

Soft-deletes a lead.

**Permission Required:** `delete leads`

---

### Convert Lead Form

**GET** `/leads/{id}/convert`

Returns the lead conversion form pre-filled with lead data.

**Permission Required:** `convert leads`

**Restrictions:** Lead must have status "won" and not already converted.

**Response Props:**
```json
{
    "lead": {...},
    "customerStatuses": {
        "active": "Active",
        "inactive": "Inactive",
        "churned": "Churned"
    },
    "users": [...],
    "businesses": [...],
    "countries": {...}
}
```

---

### Convert Lead to Customer

**POST** `/leads/{id}/convert`

Converts a won lead to a customer.

**Permission Required:** `convert leads`

**Restrictions:** Lead must have status "won" and not already converted.

**Request Body:**
```json
{
    "name": "John Prospect",
    "email": "john@prospect.com",
    "phone": "+1-555-0100",
    "company": "Prospect Inc",
    "address": "123 Customer St",
    "city": "New York",
    "state": "NY",
    "postal_code": "10001",
    "country": "US",
    "status": "active",
    "notes": "Enterprise customer",
    "assigned_to": 1,
    "business_id": 1
}
```

**Result:** Creates a new customer with `converted_from_lead_id` set to the lead's ID.

---

## Customer Management Routes

All routes require authentication and appropriate permissions.

### List Customers

**GET** `/customers`

Returns paginated list of customers.

**Permission Required:** `view customers`

**Response Props:**
```json
{
    "customers": {
        "data": [
            {
                "id": 1,
                "name": "Jane Customer",
                "email": "jane@customer.com",
                "phone": "+1-555-0200",
                "company": "Customer Corp",
                "status": "active",
                "converted_from_lead_id": 1,
                "assignee": {"id": 1, "name": "Account Manager"},
                "business": {"id": 1, "name": "Acme Corp"},
                "created_at": "2024-01-15T10:30:00Z"
            }
        ],
        "links": [...],
        "meta": {...}
    },
    "statuses": {
        "active": "Active",
        "inactive": "Inactive",
        "churned": "Churned"
    }
}
```

---

### Create Customer Form

**GET** `/customers/create`

Returns the create customer form with options.

**Permission Required:** `create customers`

**Response Props:**
```json
{
    "statuses": {...},
    "users": [...],
    "businesses": [...],
    "countries": {...}
}
```

---

### Store Customer

**POST** `/customers`

Creates a new customer directly (not from lead conversion).

**Permission Required:** `create customers`

**Request Body:**
```json
{
    "name": "Jane Customer",
    "email": "jane@customer.com",
    "phone": "+1-555-0200",
    "company": "Customer Corp",
    "address": "456 Customer Ave",
    "city": "Los Angeles",
    "state": "CA",
    "postal_code": "90001",
    "country": "US",
    "status": "active",
    "notes": "VIP customer",
    "assigned_to": 1,
    "business_id": 1
}
```

**Required Fields:** `name`

---

### Show Customer

**GET** `/customers/{id}`

Returns customer details with relationships.

**Permission Required:** `view customers`

**Response Props:**
```json
{
    "customer": {
        "id": 1,
        "name": "Jane Customer",
        "email": "jane@customer.com",
        "phone": "+1-555-0200",
        "company": "Customer Corp",
        "address": "456 Customer Ave",
        "city": "Los Angeles",
        "state": "CA",
        "postal_code": "90001",
        "country": "US",
        "status": "active",
        "notes": "VIP customer",
        "converted_from_lead_id": 1,
        "lead": {"id": 1, "name": "Jane Prospect", "source": "referral"},
        "assignee": {"id": 1, "name": "Account Manager"},
        "business": {"id": 1, "name": "Acme Corp"},
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    },
    "statuses": {...}
}
```

---

### Edit Customer Form

**GET** `/customers/{id}/edit`

Returns the edit customer form.

**Permission Required:** `edit customers`

---

### Update Customer

**PUT** `/customers/{id}`

Updates customer information.

**Permission Required:** `edit customers`

**Request Body:** Same as Store Customer with `status` required.

---

### Delete Customer

**DELETE** `/customers/{id}`

Soft-deletes a customer.

**Permission Required:** `delete customers`

---

## Shared Data (Available on All Pages)

The following data is shared via Inertia on every request:

```json
{
    "auth": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "admin"
        }
    },
    "flash": {
        "success": "Operation completed successfully",
        "error": "An error occurred",
        "status": "Status message"
    }
}
```

## Error Responses

### Validation Errors (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "name": ["The name must be at least 2 characters."]
    }
}
```

### Unauthorized (401)

Redirects to `/login` for web routes.

### Forbidden (403)

```json
{
    "message": "This action is unauthorized."
}
```

### Not Found (404)

```json
{
    "message": "No query results for model [App\\Models\\Business] 999"
}
```

### Rate Limited (429)

```json
{
    "message": "Too many login attempts. Please try again in 60 seconds."
}
```
