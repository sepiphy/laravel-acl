<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 *
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    'eloquent' => [

        'user' => class_exists(App\Models\User::class) ? App\Models\User::class : App\User::class,

        'role' => class_exists(App\Models\Role::class) ? App\Models\Role::class : Sepiphy\Laravel\Acl\Eloquent\Role::class,

        'permission' => class_exists(App\Models\Permission::class) ? App\Models\Permission::class : Sepiphy\Laravel\Acl\Eloquent\Permission::class,

    ],

    'table' => [

        'roles' => 'roles',

        'role_user' => 'role_user',

        'permissions' => 'permissions',

        'permission_role' => 'permission_role',

    ],

];
