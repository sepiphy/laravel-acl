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

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use Sepiphy\Laravel\Acl\Eloquent\Role;
use Sepiphy\Laravel\Acl\Eloquent\Permission;
use Sepiphy\Laravel\Acl\HasAcl;

class HasAclTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $user = new User();
        $user->clearBeforeHasRoleCallbacks();
        $user->clearBeforeHasPermissionCallbacks();
    }

    protected function getPackageProviders($app)
    {
        return [\Sepiphy\Laravel\Acl\AclServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'default');
        $app['config']->set('database.connections.default', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    private function createTables(): void
    {
        $migrations = collect([
            CreateUsersTable::class,
            \CreateRolesTable::class,
            \CreateRoleUserTable::class,
            \CreatePermissionsTable::class,
            \CreatePermissionRoleTable::class,
        ]);

        $migrations->each(function (string $migration) {
            $this->app[$migration]->down();
        });

        $migrations->each(function (string $migration) {
            $this->app[$migration]->up();
        });
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

    public function testHasRoleReceivesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$role must be a string or an instance of Sepiphy\Laravel\Acl\Contracts\RoleInterface. [integer] was given.');

        $user = new class() { use HasAcl; };
        $user->hasRole(123);
    }

    public function testHasRolesReceivesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$role must be a string or an instance of Sepiphy\Laravel\Acl\Contracts\RoleInterface. [integer] was given.');

        $user = new class() { use HasAcl; };
        $user->hasRoles([123]);
    }

    public function testHasPermissionReceivesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$permission must be a string or an instance of Sepiphy\Laravel\Acl\Contracts\PermissionInterface. [integer] was given.');

        $user = new class() { use HasAcl; };
        $user->hasPermission(123);
    }

    public function testHasPermissionsReceivesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$permission must be a string or an instance of Sepiphy\Laravel\Acl\Contracts\PermissionInterface. [integer] was given.');

        $user = new class() { use HasAcl; };
        $user->hasPermissions([123]);
    }

    public function testHasAllRoles()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer'],
                    (object) ['code' => 'sysadmin'],
                ]);
            }
        };

        $this->assertTrue($user->hasRoles(['developer', 'sysadmin'], true));

        $this->assertFalse($user->hasRoles(['manager', 'developer'], true));
    }

    public function testHasAnyRoles()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer'],
                    (object) ['code' => 'sysadmin'],
                ]);
            }
        };

        $this->assertTrue($user->hasRoles(['developer', 'sysadmin']));
        $this->assertTrue($user->hasRoles(['manager', 'developer', 'tester']));

        $this->assertFalse($user->hasRoles(['tester', 'ba', 'pm']));
    }

    public function testHasAllPermissions()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                        (object) ['code' => 'viewScreen2'],
                        (object) ['code' => 'viewScreen3'],
                    ])],
                ]);
            }
        };

        $this->assertTrue($user->hasPermissions(['viewScreen1', 'viewScreen2'], true));
        $this->assertTrue($user->hasPermissions(['viewScreen1', 'viewScreen2', 'viewScreen3'], true));

        $this->assertFalse($user->hasPermissions(['viewScreen1', 'viewScreen2', 'viewScreen3', 'viewScreen4'], true));
    }

    public function testHasAnyPermissions()
    {
        $user = new class() {
            use HasAcl;

            protected $roles;

            public function __construct()
            {
                $this->roles = collect([
                    (object) ['code' => 'developer', 'permissions' => collect([
                        (object) ['code' => 'viewScreen1'],
                        (object) ['code' => 'viewScreen2'],
                        (object) ['code' => 'viewScreen3'],
                    ])],
                ]);
            }
        };

        $this->assertTrue($user->hasPermissions(['viewScreen1', 'viewScreen2', 'viewScreen4', 'viewScreen5']));

        $this->assertFalse($user->hasPermissions(['viewScreen4', 'viewScreen5', 'viewScreen6']));
    }

    public function testAssignRoleWithString()
    {
        $user = User::create(['name' => 'Quynh']);

        $this->assertFalse($user->hasRole('manager'));

        $role = Role::create(['code' => 'manager', 'name' => 'Manager']);

        $user->assignRole('manager');

        $this->assertTrue($user->hasRole('manager'));
    }

    public function testAssignRoleWithObject()
    {
        $user = User::create(['name' => 'Quynh']);

        $this->assertFalse($user->hasRole('manager'));

        $role = Role::create(['code' => 'manager', 'name' => 'Manager']);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('manager'));
    }

    public function testAssignRoleReceivesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$role must be a string or an instance of Sepiphy\Laravel\Acl\Contracts\RoleInterface. [integer] was given.');

        $user = User::create(['name' => 'Quynh']);

        $user->assignRole(12345);
    }

    public function testRevokeRole()
    {
        // TODO
    }

    public function testAssignPermission()
    {
        $user = User::create(['name' => 'Quynh']);
        Permission::create(['code' => 'view-product-list', 'name' => 'View Product List']);

        $this->assertFalse($user->hasPermission('view-product-list'));

        $user->assignPermission('view-product-list');

        $this->assertTrue($user->hasPermission('view-product-list'));
    }

    public function testRevokePermission()
    {
        $user = User::create(['name' => 'Quynh']);
        Permission::create(['code' => 'view-product-list', 'name' => 'View Product List']);
        $user->assignPermission('view-product-list');

        $user->revokePermission('view-product-list');

        $this->assertFalse($user->hasPermission('view-product-list'));
    }
}

class User extends Model
{
    use HasAcl;

    protected $fillable = ['name'];
}

class CreateUsersTable
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
