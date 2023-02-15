<?php

namespace Volcano\Bus;

use Volcano\Support\ServiceProvider;


class BusServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Volcano\Bus\Dispatcher', function ($app)
        {
            return new Dispatcher($app, function ($connection = null) use ($app)
            {
                return $app->make('queue')->connection($connection);
            });
        });

        $this->app->alias(
            'Volcano\Bus\Dispatcher', 'Volcano\Bus\DispatcherInterface'
        );

        $this->app->alias(
            'Volcano\Bus\Dispatcher', 'Volcano\Bus\QueueingDispatcherInterface'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'Volcano\Bus\Dispatcher',
            'Volcano\Bus\DispatcherInterface',
            'Volcano\Bus\QueueingDispatcherInterface',
        ];
    }
}
