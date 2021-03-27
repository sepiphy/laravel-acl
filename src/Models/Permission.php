<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

class Permission extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['code', 'name', 'permission'];

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('acl.model.role'));
    }
}
