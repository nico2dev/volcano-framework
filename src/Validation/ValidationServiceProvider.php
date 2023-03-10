<?php

namespace Volcano\Validation;

use Volcano\Support\ServiceProvider;

use Volcano\Validation\DatabasePresenceVerifier;
use Volcano\Validation\Factory;


class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the Provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPresenceVerifier();

        $this->app->singleton('validator', function($app)
        {
            $config = $app['config'];

            // Get a Validation Factory instance.
            $validator = new Factory($config);

            if (isset($app['validation.presence'])) {
                $presenceVerifier = $app['validation.presence'];

                $validator->setPresenceVerifier($presenceVerifier);
            }

            return $validator;
        });
    }

    /**
     * Register the Database Presence Verifier.
     *
     * @return void
     */
    protected function registerPresenceVerifier()
    {
        $this->app->singleton('validation.presence', function($app)
        {
            return new DatabasePresenceVerifier($app['db']);
        });
    }

    /**
     * Get the services provided by the Provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('validator');
    }
}
