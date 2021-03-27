<?php declare(strict_types=1);

/*
 * This file is part of the Seriquynh package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sepiphy\Laravel\Acl\UserInterface;
use Sepiphy\Laravel\Acl\Exceptions\UnauthorizedException;

class EnsureUserHasPermission
{
    /**
     * The authenticated user has roles and permissions.
     *
     * @var \Sepiphy\Laravel\Acl\UserInterface
     */
    protected $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function handle(Request $request, Closure $next, string $permission)
    {
        if ($this->user->hasPermission($permission)) {
            return $next($request);
        }

        throw new UnauthorizedException(403, 'Unauthorized');
    }
}
