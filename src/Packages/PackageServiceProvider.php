<?php

namespace Volcano\Packages;

use Volcano\Packages\PackageManager;
use Volcano\Packages\Repository;
use Volcano\Support\ServiceProvider;


class PackageServiceProvider extends ServiceProvider
{
    /**
     * @var bool Indicates if loading of the Provider is deferred.
     */
    protected $defer = false;

    /**
     * Boot the Service Provider.
     */
    public function boot()
    {
        $packages = $this->app['packages'];

        $packages->register();
    }

    /**
     * Register the Service Provider.
     */
    public function register()
    {
        $this->app->singleton('packages', function ($app)
        {
            $repository = new Repository($app['config'], $app['files']);

            return new PackageManager($app, $repository);
        });
    }

    /**
     * Get the Services provided by the Provider.
     *
     * @return string
     */
    public function provides()
    {
        return array('packages');
    }

}
