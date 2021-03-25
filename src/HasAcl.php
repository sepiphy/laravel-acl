<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 *
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl;

use Illuminate\Support\Facades\Config;
use Sepiphy\Laravel\Acl\Eloquent\Permission;
use Sepiphy\Laravel\Acl\Eloquent\Role;

trait HasAcl
{
    /**
     * @var callable[]
     */
    protected static $beforeHasRoleCallbacks = [];

    /**
     * @var callable[]
     */
    protected static $beforeHasPermissionCallbacks = [];

    /**
     * @param  callable  $callback
     * @return void
     */
    public static function beforeHasRole($callback)
    {
        static::$beforeHasRoleCallbacks[] = $callback;
    }

    /**
     * @param  callable  $callback
     * @return void
     */
    public static function beforeHasPermission($callback)
    {
        static::$beforeHasPermissionCallbacks[] = $callback;
    }

    /**
     * @return void
     */
    public static function clearBeforeHasPermissionCallbacks()
    {
        static::$beforeHasPermissionCallbacks = [];
    }

    /**
     * @return void
     */
    public static function clearBeforeHasRoleCallbacks()
    {
        static::$beforeHasRoleCallbacks = [];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('acl.eloquent.role'));
    }

    /**
     * @param  string|Role  $role
     * @return bool
     */
    public function hasRole($role)
    {
        $role = $role instanceof Role ? $role->code : $role;

        foreach (static::$beforeHasRoleCallbacks as $callback) {
            $result = $callback($role, $this);

            if (is_bool($result)) {
                return $result;
            }
        }

        return $this->roles->contains('code', $role);
    }

    /**
     * @param  string|Permission  $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        $permission = $permission instanceof Permission ? $permission->code : $permission;

        foreach (static::$beforeHasPermissionCallbacks as $callback) {
            $result = $callback($permission, $this);

            if (is_bool($result)) {
                return $result;
            }
        }

        return $this->roles->contains(function ($role) use ($permission) {
            return $role->permissions->contains('code', $permission);
        });
    }
}
