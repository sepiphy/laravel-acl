# Laravel Access Control List

![Tests](https://img.shields.io/github/workflow/status/sepiphy/laravel-acl/tests?label=tests)
![Packagist](https://img.shields.io/packagist/dt/sepiphy/laravel-acl.svg)
![Packagist Version](https://img.shields.io/packagist/v/sepiphy/laravel-acl.svg?label=version)
![GitHub](https://img.shields.io/github/license/sepiphy/laravel-acl.svg)

| sepiphy/laravel-acl | Laravel version | Status |
|---------|-----------------------------|--------|
| 3.x | `^6.0`, `^7.0`, `^8.0` | `maintain`
| 2.x | `^5.8`, `^6.0`, `^7.0` | `deprecated`

## Installation

Install php dependencies:

```bash
composer require sepiphy/laravel-acl
```

Create acl tables including `roles`, `role_user`, `permissions` and `permission_role`. You can change names by changing values of acl.php config file.

```bash
php artisan migrate
```

Define `User` model class using ACL trait and user interace:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Sepiphy\Laravel\Acl\UserInterface;
use Sepiphy\Laravel\Acl\HasRolesPermissions;

class User extends Authenticatable implements UserInterface
{
    use HasRolesPermissions, HasFactory, Notifiable;

    //
}

```

## Usage

Prepare a few roles and permissions:

```php
<?php

use Sepiphy\Laravel\Acl\Models\Permission;
use Sepiphy\Laravel\Acl\Models\Role;

Role::insert([
    ['code' => 'manager', 'name' => 'Manager', 'description' => 'For a person who manages teams'],
    ['code' => 'developer', 'name' => 'Developer', 'description' => 'For a person who codes'],
    ['code' => 'tester', 'name' => 'Tester', 'description' => 'For a person who tests'],
]);

Permission::insert([
    ['code' => 'view-product-list', 'name' => 'View Product List', 'description' => ''],
    ['code' => 'view-product-detail', 'name' => 'View Product Detail', 'description' => ''],
    ['code' => 'create-product', 'name' => 'Create Product', 'description' => ''],
]);
```

Attach, check and revoke roles or permissions:

```php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Sepiphy\Laravel\Acl\Models\Permission;
use Sepiphy\Laravel\Acl\Models\Role;

$user = User::firstOrCreate(['email' => 'seriquynh@gmail.com'], [
    'name' => 'Quynh',
    'password' => Hash::make('secret'),
]);

// Assign a role to a user.
$user->hasRole('developer'); // false
$user->assignRole('developer');
$user->hasRole('developer'); // true

// Assign permissions to a role.
$user->hasPermission('view-product-list'); // false
$user->hasPermission('view-product-detail'); // false

$role = Role::whereCode('developer')->first();
$role->permissions()->attach(
    Permission::whereIn('code', ['view-product-list', 'view-product-detail'])->pluck('id')->toArray()
);

$user->hasPermission('view-product-list'); // true
$user->hasPermission('view-product-detail'); // true
$user->hasPermission('create-product'); // false

// Assign permission directly to a user.
$user->assignPermission('create-product');
$user->hasPermission('create-product'); // true

// Revoke a role from a user.
$user->revokeRole('developer');
$user->hasRole('developer'); // false

// Revoke a permission from a user.
$user->revokePermission('create-product');
$user->hasPermission('create-product'); // false
```

Define `role` and `permission` middleware:

```php
<?php

namespace App\Http;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        /** Other middleware */
        'role' => \Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasRole::class,
        'permission' => \Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasPermission::class,
    ];
}
```

Register a few routes that require `role` or `permission` middleware:

```php
<?php

Route::get('/report-dashboard', [
    'middleware' => 'role:manager',
    'uses' => 'App\Http\Controllers\DashboardController@index',
]);

Route::get('/products', [
    'middleware' => 'permission:view-product-list',
    'uses' => 'App\Http\Controllers\ProductController@index',
]);
```

Use [ACL](./src/Facade.php) facade for quickly access.
