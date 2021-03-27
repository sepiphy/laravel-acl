<?php declare(strict_types=1);

/*
 * This file is part of the Sepiphy package.
 * (c) Quynh Xuan Nguyen <seriquynh@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sepiphy\Laravel\Acl;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasPermission;
use Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasRole;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/acl.php', 'acl');

        $this->app->singleton(UserInterface::class, function ($app) {
            return $app['auth']->user();
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
            __DIR__.'/../config' => $this->app->configPath(),
            __DIR__.'/../migrations' => $this->app->databasePath('migrations'),
        ], 'laravel-acl');
    }
}
