<?php

namespace Volcano\Events;

use Volcano\Support\ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    
    /**
     * Enregistrez le fournisseur de services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['events'] = $this->app->share(function($app)
        {
            return with(new Dispatcher($app))->setQueueResolver(function () use ($app)
            {
                return $app['queue'];
            });
        });
    }

}
