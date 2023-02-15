<?php

namespace Volcano\Broadcasting;

use Volcano\Broadcasting\BroadcastManager;

use Volcano\Support\ServiceProvider;


class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Volcano\Broadcasting\BroadcastManager', function ($app)
        {
            return new BroadcastManager($app);
        });

        $this->app->singleton('Volcano\Broadcasting\BroadcasterInterface', function ($app)
        {
            return $app->make('Volcano\Broadcasting\BroadcastManager')->connection();
        });

        $this->app->alias(
            'Volcano\Broadcasting\BroadcastManager', 'Volcano\Broadcasting\FactoryInterface'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'Volcano\Broadcasting\BroadcastManager',
            'Volcano\Broadcasting\FactoryInterface',
            'Volcano\Broadcasting\BroadcasterInterface',
        );
    }
}
