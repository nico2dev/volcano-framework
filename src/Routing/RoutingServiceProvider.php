<?php

namespace Volcano\Routing;

//use Volcano\Filesystem\Filesystem;

use Volcano\Routing\ResponseFactory;
use Volcano\Routing\Router;
use Volcano\Routing\Redirector;
use Volcano\Routing\UrlGenerator;

use Volcano\Support\ServiceProvider;


class RoutingServiceProvider extends ServiceProvider
{

    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouter();

        $this->registerUrlGenerator();

        $this->registerRedirector();

        $this->registerResponseFactory();

        // Register the additional service providers.
        $this->app->register('Volcano\Routing\Assets\AssetServiceProvider');
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app)
        {
            return new Router($app['events'], $app);
        });
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app)
        {
            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $routes = $app['router']->getRoutes();

            $url = new UrlGenerator($routes, $app->rebinding('request', function ($app, $request)
            {
                $app['url']->setRequest($request);
            }));

            $url->setSessionResolver(function ()
            {
                return $this->app['session'];
            });

            return $url;
        });
    }

    /**
     * Register the Redirector service.
     *
     * @return void
     */
    protected function registerRedirector()
    {
        $this->app->singleton('redirect', function ($app)
        {
            $redirector = new Redirector($app['url']);

            // If the session is set on the application instance, we'll inject it into
            // the redirector instance. This allows the redirect responses to allow
            // for the quite convenient "with" methods that flash to the session.
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }

            return $redirector;
        });
    }

    /**
     * Register the response factory implementation.
     *
     * @return void
     */
    protected function registerResponseFactory()
    {
        $this->app->singleton('response.factory', function ($app)
        {
            return new ResponseFactory();
        });
    }
}
