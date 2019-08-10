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

trait HasAcl
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('acl.eloquent.role'));
    }

    /**
     * @param  string  $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->roles->contains('code', $role);
    }

    /**
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return $this->roles->contains(function ($role) use ($permission) {
            return $role->permissions->contains('code', $permission);
        });
    }
}
