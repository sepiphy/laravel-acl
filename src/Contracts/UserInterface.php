<?php

namespace Sepiphy\Laravel\Acl\Contracts;

interface UserInterface
{
    /**
     * @param  string  $role
     * @return bool
     */
    public function hasRole(string $role): bool;

    /**
     * @param  string[]  $roles
     * @param  bool  $requireAll
     * @return bool
     */
    public function hasRoles($roles, bool $requireAll = false): bool;

    /**
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool;

    /**
     * @param  string[]  $permissions
     * @param  bool  $requireAll
     * @return bool
     */
    public function hasPermissions($permissions, bool $requireAll = false): bool;
}
