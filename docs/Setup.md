# CRM Base Kit - Setup Guide

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 20+
- NPM 10+
- MySQL 8.x

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url> crm-basekit
cd crm-basekit
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_basekit
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Create Database

Create the MySQL database:

```sql
CREATE DATABASE crm_basekit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed Roles and Permissions

This is required to set up the RBAC system:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

This creates:
- **26 permissions** across all modules
- **5 roles**: super-admin, admin, manager, sales, user

### 8. Create Admin User (Optional)

Use tinker to create your first admin user:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
$user->assignRole('super-admin');
```

Or register through the UI at `/register` (users get 'user' role by default).

### 9. Build Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 10. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default Routes

| Route | Description | Auth Required |
|-------|-------------|---------------|
| `/login` | Login page | No |
| `/register` | Registration page | No |
| `/forgot-password` | Password reset request | No |
| `/` | Dashboard | Yes |
| `/businesses` | Business management | Yes + Permission |
| `/users` | User management | Yes + Permission |
| `/leads` | Lead management | Yes + Permission |
| `/leads/{id}/convert` | Convert lead to customer | Yes + Permission |
| `/customers` | Customer management | Yes + Permission |

## Development Workflow

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage
```

### Static Analysis

```bash
php vendor/bin/phpstan analyse --memory-limit=512M
```

### Code Style

```bash
# Check code style
php vendor/bin/pint --test

# Fix code style
php vendor/bin/pint
```

### Full Quality Check

Run all checks before committing:

```bash
php vendor/bin/pint && php vendor/bin/phpstan analyse --memory-limit=512M && php artisan test
```

### Asset Compilation

```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

## Role Permissions Matrix

| Permission | super-admin | admin | manager | sales | user |
|------------|:-----------:|:-----:|:-------:|:-----:|:----:|
| view users | x | x | x | | |
| create users | x | x | | | |
| edit users | x | x | | | |
| delete users | x | | | | |
| view businesses | x | x | x | | |
| create businesses | x | x | x | | |
| edit businesses | x | x | x | | |
| delete businesses | x | x | | | |
| view leads | x | x | x | x | x |
| create leads | x | x | x | x | |
| edit leads | x | x | x | x | |
| delete leads | x | x | | | |
| convert leads | x | x | x | x | |
| view customers | x | x | x | x | x |
| create customers | x | x | x | x | |
| edit customers | x | x | x | x | |
| delete customers | x | x | | | |
| view news | x | x | x | x | x |
| create news | x | x | x | | |
| edit news | x | x | x | | |
| delete news | x | x | | | |
| publish news | x | x | | | |

## IDE Setup

### VS Code Extensions
- Laravel Extension Pack
- PHP Intelephense
- ESLint
- Prettier
- Tailwind CSS IntelliSense (for Bootstrap classes)

### PHPStorm
- Enable Laravel plugin
- Configure PHP interpreter
- Set up Pest as test framework

## Troubleshooting

### MySQL Key Length Error

If you encounter "Specified key was too long" error, the fix is already applied in `AppServiceProvider`:

```php
use Illuminate\Database\Schema\Builder;

public function boot(): void
{
    Builder::defaultStringLength(191);
}
```

### Vite Not Loading

Ensure Vite dev server is running:
```bash
npm run dev
```

Or build for production:
```bash
npm run build
```

### Permission Cache Issues

Clear permission cache after changes:
```bash
php artisan permission:cache-reset
```

### Clear All Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Errors on Storage

On Linux/Mac:
```bash
chmod -R 775 storage bootstrap/cache
```

On Windows, ensure the web server has write access to these directories.

### Login Rate Limiting

Login is rate-limited to 5 attempts per minute per IP/email combination. Wait 60 seconds or clear the rate limiter:

```bash
php artisan cache:clear
```

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm run build`
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Run `php artisan view:cache`
7. Set up proper file permissions
8. Configure queue worker for background jobs
9. Set up scheduler for cron jobs
