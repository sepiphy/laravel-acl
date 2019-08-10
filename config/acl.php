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
        'user' => App\User::class,

        'role' => Sepiphy\Laravel\Acl\Eloquent\Role::class,

        'permission' => Sepiphy\Laravel\Acl\Eloquent\Permission::class,
    ],

    'table' => [
        'roles' => 'roles',

        'role_user' => 'role_user',

        'permissions' => 'permissions',

        'permission_role' => 'permission_role',
    ],

];
