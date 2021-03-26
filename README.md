# sepiphy/laravel-acl

![Packagist](https://img.shields.io/packagist/dt/sepiphy/laravel-acl.svg)
![Packagist Version](https://img.shields.io/packagist/v/sepiphy/laravel-acl.svg?label=version)
![GitHub](https://img.shields.io/github/license/sepiphy/laravel-acl.svg)

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
use Sepiphy\Laravel\Acl\Contracts\UserInterface;
use Sepiphy\Laravel\Acl\HasAcl;

class User extends Authenticatable implements UserInterface
{
    use HasAcl, HasFactory, Notifiable;

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

$user = User::whereEmail('developer@example.com')->first();

// Assign a role to a user.
$user->assignRole('developer');
$user->hasRole('developer'); // true

// Assign permissions to a role.
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
