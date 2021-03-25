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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Sepiphy\Laravel\Acl\Contracts\PermissionInterface;
use Sepiphy\Laravel\Acl\Contracts\RoleInterface;
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
        if (!is_string($role) && !$role instanceof RoleInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    '$role must be a string or an instance of %s. [%s] was given.',
                    RoleInterface::class,
                    is_object($role) ? get_class($role) : gettype($role)
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

        if ($this instanceof Model) {
            $this->load('roles', 'roles.permissions');
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

            if ($checked) {
                if (!$requireAll) {
                    return true;
                }
                $count++;
            }
        }

        return $count === count($roles);
    }

    public function hasPermission($permission): bool
    {
        if (!is_string($permission) && !$permission instanceof PermissionInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    '$permission must be a string or an instance of %s. [%s] was given.',
                    PermissionInterface::class,
                    is_object($permission) ? get_class($permission) : gettype($permission)
                )
            );
        }

        $permission = $permission instanceof PermissionInterface ? $permission->getCode() : $permission;

        foreach (static::$beforeHasPermissionCallbacks as $callback) {
            $result = $callback($permission, $this);

            if (is_bool($result)) {
                return $result;
            }
        }

        if ($this instanceof Model) {
            $this->load('roles', 'roles.permissions');
        }

        return $this->roles->contains(function ($role) use ($permission) {
            return $role->permissions->contains('code', $permission);
        });
    }

    public function hasPermissions($permissions, bool $requireAll = false): bool
    {
        $count = 0;

        foreach ($permissions as $permission) {
            $checked = $this->hasPermission($permission);

            if ($checked) {
                if (!$requireAll) {
                    return true;
                }

                $count++;
            }
        }

        return $count === count($permissions);
    }

    public function assignRole($role)
    {
        if (!is_string($role) && !$role instanceof RoleInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    '$role must be a string or an instance of %s. [%s] was given.',
                    RoleInterface::class,
                    is_object($role) ? get_class($role) : gettype($role)
                )
            );
        }

        $code = $role instanceof RoleInterface ? $role->getCode() : $role;

        $roleModel = app(Config::get('acl.eloquent.role'));

        $role = $roleModel->whereCode($code)->firstOrFail();

        return $this->roles()->attach($role->getKey());
    }

    public function revokeRole($role)
    {
        $code = $role instanceof RoleInterface ? $role->getCode() : $role;

        $roleModel = app(Config::get('acl.eloquent.role'));

        $role = $roleModel->whereCode($code)->firstOrFail();

        return $this->roles()->detach($role->getKey());
    }

    public function assignPermission($permission)
    {
        $code = $permission instanceof PermissionInterface ? $role->getCode() : $permission;

        $permissionModel = app(Config::get('acl.eloquent.permission'));

        $permission = $permissionModel->whereCode($code)->firstOrFail();

        return $this->getDefaultRole()->permissions()->attach($permission->getKey());
    }

    protected function getDefaultRole(): Role
    {
        $code = $this->getKey().'_default';

        $role = Role::firstOrCreate(['code' => $code], ['name' => $code, 'hidden' => true]);

        if (!$this->hasRole($code)) {
            $this->roles()->attach($role->getKey());
        }

        return $role;
    }

    public function revokePermission($permission)
    {
        $code = $permission instanceof PermissionInterface ? $role->getCode() : $permission;

        $permissionModel = app(Config::get('acl.eloquent.permission'));

        $permission = $permissionModel->whereCode($code)->firstOrFail();

        return $this->getDefaultRole()->permissions()->detach($permission->getKey());
    }
}
