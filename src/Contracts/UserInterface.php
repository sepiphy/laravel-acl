<?php

namespace Sepiphy\Laravel\Acl\Contracts;

use InvalidArgumentException;

interface UserInterface
{
    /**
     * @param  string|RoleInterface  $role
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasRole($role): bool;

    /**
     * @param  string[]|RoleInterface[]  $roles
     * @param  bool  $requireAll
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasRoles($roles, bool $requireAll = false): bool;

    /**
     * @param  string|PermissionInterface  $permission
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasPermission($permission);

    /**
     * @param  string[]|PermissionInterface[]  $permissions
     * @param  bool  $requireAll
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasPermissions($permissions, bool $requireAll = false): bool;
}
