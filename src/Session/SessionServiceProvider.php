<?php

namespace Volcano\Session;

use Volcano\Support\ServiceProvider;


class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->setupDefaultDriver();

        $this->registerSessionManager();

        $this->registerSessionDriver();

        //
        $this->app->singleton('Volcano\Session\Middleware\StartSession');
    }

    /**
     * Setup the default session driver for the application.
     *
     * @return void
     */
    protected function setupDefaultDriver()
    {
        if ($this->app->runningInConsole()) {
            $this->app['config']['session.driver'] = 'array';
        }
    }

    /**
     * Register the session manager instance.
     *
     * @return void
     */
    protected function registerSessionManager()
    {
        $this->app->singleton('session', function($app)
        {
            return new SessionManager($app);
        });
    }

    /**
     * Register the session driver instance.
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
        $this->app->bindShared('session.store', function($app)
        {
            $manager = $app['session'];

            return $manager->driver();
        });
    }

}
