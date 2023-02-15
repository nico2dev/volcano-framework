<?php

namespace Volcano\Exception;

use Volcano\Exception\Handler as ExceptionHandler;

use Volcano\Support\ServiceProvider;


class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Enregistrez le fournisseur de services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['exception'] = $this->app->share(function ($app)
        {
            return new ExceptionHandler($app);
        });
    }
}