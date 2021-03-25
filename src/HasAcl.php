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
     * @param  string|RoleInterface  $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (!is_string($role) || !$role instanceof RoleInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    '$role must be a string or an instanceof %s. [%s] was given.',
                    [RoleInterface::class, is_object($role) ? get_class($role) : get_type($role)]
                )
            );
        }

        $role = $role instanceof RoleInterface ? $role->getCode() : $role;

        foreach (static::$beforeHasRoleCallbacks as $callback) {
            $result = $callback($role, $this);

            if (is_bool($result)) {
                return $result;
            }
        }

        return $this->roles->contains('code', $role);
    }

    /**
     * @param  string[]|RoleInterface[]  $roles
     * @param  bool  $requireAll
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasRoles($roles, bool $requireAll = false): bool
    {
        $count = 0;

        foreach ($roles as $role) {
            $checked = $this->hasRole($role);

            if ($checked && !$requireAll) {
                return true;
            }

            $count++;
        }

        return $count === count($roles);
    }

    public function hasPermission($permission): bool;
    {
        if (!is_string($role) || !$role instanceof PermissionInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    '$permission must be a string or an instanceof %s. [%s] was given.',
                    [PermissionInterface::class, is_object($permission) ? get_class($permission) : get_type($permission)]
                )
            );
        }

        $permission = $permission instanceof RoleInterface ? $permission->getCode() : $permission;

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

    public function hasPermissions($permissions, bool $requireAll = false): bool;
    {
        $count = 0;

        foreach ($permissions as $permission) {
            $checked = $this->hasPermission($permission);

            if ($checked && !$requireAll) {
                return true;
            }

            $count++;
        }

        return $count === count($permissions);
    }
}
