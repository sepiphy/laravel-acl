<?php declare(strict_types=1);

/*
 * This file is part of the Seriquynh package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

class Role extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['code', 'name', 'description', 'hidden'];

    /**
     * @return HasMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('acl.model.user'));
    }

    /**
     * @return BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Config::get('acl.model.permission'));
    }
}
