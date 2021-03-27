<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Sepiphy\Laravel\Acl\Contracts\PermissionInterface;
use Sepiphy\Laravel\Acl\Contracts\RoleInterface;
use Sepiphy\Laravel\Acl\Models\Permission;
use Sepiphy\Laravel\Acl\Models\Role;

trait HasRolesPermissions
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
        return $this->belongsToMany(Config::get('acl.model.role'));
    }

    public function hasRole(string $role): bool
    {
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

    public function hasPermission(string $permission): bool
    {
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

    public function assignRole(string $role)
    {
        $role = $this->newRoleModel()->whereCode($role)->firstOrFail();

        $this->roles()->attach($role->getKey());
    }

    public function assignRoles($roles)
    {
        $roleModel = $this->newRoleModel();

        $roleKeys = $roleModel->whereIn('code', $roles)->pluck($roleModel->getKeyName());

        $this->roles()->attach($roleKeys);
    }

    private function newRoleModel(): Role
    {
        return app(Config::get('acl.model.role'));
    }

    public function revokeRole(string $role)
    {
        $role = $this->newRoleModel()->whereCode($role)->firstOrFail();

        $this->roles()->detach($role->getKey());
    }

    public function revokeRoles($roles)
    {
        $roleModel = $this->newRoleModel();

        $roleKeys = $roleModel->whereIn('code', $roles)->pluck($roleModel->getKeyName());

        $this->roles()->detach($roleKeys);
    }

    public function assignPermission(string $permission)
    {
        $permission = $this->newPermissionModel()->whereCode($permission)->firstOrFail();

        $this->getDefaultRole()->permissions()->attach($permission->getKey());
    }

    public function assignPermissions($permissions)
    {
        $permissionModel = $this->newPermissionModel();

        $permissionKeys = $permissionModel->whereIn('code', $permissions)->pluck(
            $permissionModel->getKeyName()
        );

        $this->getDefaultRole()->permissions()->attach($permissionKeys);
    }

    private function newPermissionModel()
    {
        return app(Config::get('acl.model.permission'));
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

    public function revokePermission(string $permission)
    {
        $permission = $this->newPermissionModel()->whereCode($permission)->firstOrFail();

        $this->getDefaultRole()->permissions()->detach($permission->getKey());
    }

    public function revokePermissions($permissions)
    {
        $permissionModel = $this->newPermissionModel();

        $permissionKeys = $permissionModel->whereIn('code', $permissions)->pluck($permissionModel->getKeyName());

        $this->getDefaultRole()->permissions()->detach($permissionKeys);
    }
}
