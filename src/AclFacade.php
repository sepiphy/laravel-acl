<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasRole(string $role)
 * @method static bool hasRoles(string[] $roles, bool $requireAll = false)
 * @method static bool hasPermission(string $permission)
 * @method static bool hasPermissions(string[] $permissions, bool $requireAll = false)
 *
 * @see Sepiphy\Laravel\Acl\UserInterface
 */
class AclFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UserInterface::class;
    }

    public static function user(): UserInterface
    {
        return static::$app[UserInterface::class];
    }
}
