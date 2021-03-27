<?php declare(strict_types=1);

/*
 * This file is part of the Seriquynh package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasPermission;
use Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasRole;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/acl.php', 'acl');

        $this->app->singleton(UserInterface::class, function () {
            return $this->getUser();
        });

        $this->app->singleton(EnsureUserHasRole::class, function ($app) {
            return new EnsureUserHasRole($app[UserInterface::class]);
        });

        $this->app->singleton(EnsureUserHasPermission::class, function ($app) {
            return new EnsureUserHasPermission($app[UserInterface::class]);
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        $this->publishes([
            __DIR__.'/../config' => $this->app->configPath('acl'),
            __DIR__.'/../migrations' => $this->app->databasePath('migrations'),
        ], 'laravel-acl');
    }

    private function getUser()
    {
        $this->ensureUserAuthenticated();

        $this->ensureUserHasRolesPermissions();

        return $this->app['auth']->user();
    }

    private function ensureUserAuthenticated()
    {
        if (!$this->app['auth']->check()) {
            throw new RuntimeException('There is no authenticated user to use %s.', EnsureUserHasRole::class);
        }
    }

    private function ensureUserHasRolesPermissions()
    {
        $user = $this->app['auth']->user();

        if (!$user instanceof UserInterface) {
            throw new RuntimeException(
                sprintf('The authenticated user must an instance of %s.', UserInterface::class)
            );
        }
    }
}
