<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 *
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Sepiphy\Laravel\Acl;

use PHPUnit\Framework\TestCase;
use Sepiphy\Laravel\Acl\HasAcl;
use Sepiphy\Laravel\Acl\Eloquent\Role;
use Sepiphy\Laravel\Acl\Eloquent\Permission;

class HasAclTest extends TestCase
{
    protected function tearDown(): void
    {
        $user = new class() {
            use HasAcl;
        };
        $user->clearBeforeHasRoleCallbacks();
        $user->clearBeforeHasPermissionCallbacks();
    }

    public function testHasRole()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer'],
                ]);
            }
        };

        $this->assertTrue($user->hasRole('developer'));
        $this->assertFalse($user->hasRole('manager'));
    }

    public function testHasPermission()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                    ])],
                ]);
            }
        };

        $this->assertTrue($user->hasPermission('viewScreen1'));
        $this->assertFalse($user->hasPermission('viewScreen2'));
    }

    public function testBeforeHasRole()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer'],
                ]);
            }
        };

        $user->beforeHasRole(function ($role) {
            if ($role === 'developer') {
                return false;
            }
        });

        $this->assertFalse($user->hasRole('developer'));
        $this->assertFalse($user->hasRole('saler'));

        $user->beforeHasRole(function ($role) {
            if ($role === 'saler') {
                return true;
            }
        });

        $this->assertTrue($user->hasRole('saler'));
    }

    public function testBeforeHasRoleCallbackReceiveUser()
    {
        $user = new class() {
            use HasAcl;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                    ])],
                ]);
            }
        };

        $user->beforeHasRole(function ($role, $instance) use ($user) {
            $this->assertSame($user, $instance);
        });

        $this->assertTrue($user->hasRole('developer'));
    }

    public function testBeforeHasPermission()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                    ])],
                ]);
            }
        };

        $user->beforeHasPermission(function ($permission) {
            if ($permission === 'viewScreen1') {
                return false;
            }
        });

        $this->assertFalse($user->hasPermission('viewScreen1'));

        $user->beforeHasPermission(function ($permission) {
            if ($permission === 'viewScreen2') {
                return true;
            }
        });

        $this->assertTrue($user->hasPermission('viewScreen2'));
    }

    public function testBeforeHasPermissionCallbackReceiveUser()
    {
        $user = new class() {
            use HasAcl;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                    ])],
                ]);
            }
        };

        $user->beforeHasPermission(function ($permission, $instance) use ($user) {
            $this->assertSame($user, $instance);
        });

        $this->assertTrue($user->hasPermission('viewScreen1'));
    }

    public function testHasRoleReceiveRoleModel()
    {
        $user = new class() {
            use HasAcl;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                    ])],
                ]);
            }
        };

        $role = new Role(['code' => 'developer']);

        $this->assertTrue($user->hasRole($role));
    }

    public function testHasRoleReceivePermissionModel()
    {
        $user = new class() {
            use HasAcl;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                    ])],
                ]);
            }
        };

        $permission = new Permission(['code' => 'viewScreen1']);

        $this->assertTrue($user->hasPermission($permission));
    }
}
