<?php declare(strict_types=1);

/*
 * This file is part of the Seriquynh package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl;

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
