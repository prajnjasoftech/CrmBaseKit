# CRM Base Kit - Extension Guide

This guide explains how to extend the CRM Base Kit with new features and modules.

## Adding a New Module

### Step 1: Create the Migration

```bash
php artisan make:migration create_products_table
```

```php
// database/migrations/xxxx_create_products_table.php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->boolean('active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
}
```

### Step 2: Create the Model

```bash
php artisan make:model Product
```

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];
}
```

### Step 3: Create Form Request

```bash
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest
```

```php
// app/Http/Requests/StoreProductRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create products');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ];
    }
}
```

### Step 4: Create the Controller

```bash
php artisan make:controller ProductController --resource
```

```php
// app/Http/Controllers/ProductController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);

        return Inertia::render('Products/Index', [
            'products' => Product::latest()->paginate(15),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Products/Create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        Product::create($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): Response
    {
        $this->authorize('view', $product);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
```

### Step 5: Create the Policy

```bash
php artisan make:policy ProductPolicy --model=Product
```

```php
// app/Policies/ProductPolicy.php
namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view products');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('view products');
    }

    public function create(User $user): bool
    {
        return $user->can('create products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('edit products');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete products');
    }
}
```

### Step 6: Add Routes

```php
// routes/web.php
use App\Http\Controllers\ProductController;

Route::middleware(['auth'])->group(function () {
    Route::resource('products', ProductController::class);
});
```

### Step 7: Create React Pages

```jsx
// resources/js/Pages/Products/Index.jsx
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ products, auth }) {
    return (
        <AdminLayout user={auth?.user}>
            <Head title="Products" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Products</h1>
                    <p className="page-subtitle">Manage your products</p>
                </div>
                <Link href="/products/create" className="btn btn-primary">
                    <i className="bi bi-plus-lg me-2"></i>
                    Add Product
                </Link>
            </div>

            <div className="admin-card">
                <div className="card-body">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.data.map((product) => (
                                <tr key={product.id}>
                                    <td>{product.name}</td>
                                    <td>${product.price}</td>
                                    <td>
                                        <span className={`status-badge status-${product.active ? 'active' : 'inactive'}`}>
                                            {product.active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td>
                                        <Link href={`/products/${product.id}/edit`} className="btn btn-sm btn-outline-primary me-1">
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
```

### Step 8: Add to Sidebar Navigation

Update `resources/js/Components/Sidebar.jsx`:

```jsx
const menuItems = [
    // ... existing items
    {
        section: 'Catalog',
        items: [
            { name: 'Products', icon: 'bi-box', href: '/products' },
        ],
    },
];
```

### Step 9: Create Tests

```php
// tests/Feature/ProductTest.php
<?php

use App\Models\Product;
use App\Models\User;

it('displays products list', function () {
    $user = User::factory()->create();
    Product::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/products');

    $response->assertStatus(200);
});

it('can create a product', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/products', [
        'name' => 'Test Product',
        'price' => 99.99,
    ]);

    $response->assertRedirect('/products');
    $this->assertDatabaseHas('products', ['name' => 'Test Product']);
});
```

## Adding Permissions

### Step 1: Create a Seeder

```php
// database/seeders/ProductPermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assign to admin role
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo($permissions);
    }
}
```

### Step 2: Run the Seeder

```bash
php artisan db:seed --class=ProductPermissionSeeder
```

## Creating Custom Components

### React Component Example

```jsx
// resources/js/Components/ProductCard.jsx
export default function ProductCard({ product, onEdit, onDelete }) {
    return (
        <div className="admin-card">
            <div className="card-body">
                <h3 className="h5 mb-2">{product.name}</h3>
                <p className="text-muted mb-3">{product.description}</p>
                <div className="d-flex justify-content-between align-items-center">
                    <span className="h4 mb-0">${product.price}</span>
                    <div>
                        <button onClick={() => onEdit(product)} className="btn btn-sm btn-outline-primary me-1">
                            Edit
                        </button>
                        <button onClick={() => onDelete(product)} className="btn btn-sm btn-outline-danger">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
```

## Event-Driven Features

### Creating Events

```bash
php artisan make:event ProductCreated
php artisan make:listener SendProductNotification --event=ProductCreated
```

### Dispatching Events

```php
// In your controller or service
event(new ProductCreated($product));
```

## Queue Jobs

### Creating a Job

```bash
php artisan make:job ProcessProductImport
```

```php
// app/Jobs/ProcessProductImport.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath
    ) {}

    public function handle(): void
    {
        // Import logic here
    }
}
```

### Dispatching Jobs

```php
ProcessProductImport::dispatch($filePath);
```

## Best Practices

1. **Always create tests** for new features
2. **Use Form Requests** for validation
3. **Use Policies** for authorization
4. **Follow the existing patterns** for consistency
5. **Run quality checks** before committing:
   ```bash
   php vendor/bin/pint
   php vendor/bin/phpstan analyse
   php vendor/bin/pest
   ```

## Lead Conversion Pattern

When converting one entity to another (like Lead → Customer), follow this pattern:

### Controller Methods

```php
public function showConvert(Lead $lead): Response
{
    $this->authorize('convert', $lead);

    return Inertia::render('Leads/Convert', [
        'lead' => $lead,
        'customerStatuses' => Customer::getStatuses(),
    ]);
}

public function convert(ConvertLeadRequest $request, Lead $lead): RedirectResponse
{
    $this->authorize('convert', $lead);

    $data = $request->validated();
    $data['converted_from_lead_id'] = $lead->id;

    Customer::create($data);

    return redirect()->route('customers.index')
        ->with('success', 'Lead converted to customer successfully.');
}
```

### Policy Method

```php
public function convert(User $user, Lead $lead): bool
{
    return $user->can('convert leads') && $lead->canBeConverted();
}
```

### Model Helper

```php
public function canBeConverted(): bool
{
    return $this->status === self::STATUS_WON && ! $this->isConverted();
}

public function isConverted(): bool
{
    return $this->status === self::STATUS_WON && $this->customer()->exists();
}
```

### Routes

```php
Route::get('leads/{lead}/convert', [LeadController::class, 'showConvert'])->name('leads.convert');
Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert.store');
```
