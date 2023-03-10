<?php

namespace Volcano\Routing;

use Volcano\Container\Container;

use Volcano\Routing\RouteDependencyResolverTrait;

//use Closure;


class ControllerDispatcher
{
    use RouteDependencyResolverTrait;

    /**
     * The IoC container instance.
     *
     * @var \Volcano\Container\Container
     */
    protected $container;


    /**
     * Create a new controller dispatcher instance.
     *
     * @param  \Volcano\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given controller and method.
     *
     * @param  \Volcano\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method)
    {
        $parameters = $this->resolveClassMethodDependencies(
            $route->parametersWithoutNulls(), $controller, $method
        );

        if (! method_exists($controller, $callerMethod = 'callAction')) {
            return $this->run($controller, $method, $parameters);
        }

        return $this->run($controller, $callerMethod, $this->resolveClassMethodDependencies(
            array($method, $parameters), $controller, $callerMethod
        ));
    }

    /**
     * Runs the controller method and returns the response.
     *
     * @param  mixed  $controller
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    protected function run($controller, $method, $parameters)
    {
        return call_user_func_array(array($controller, $method), $parameters);
    }

    /**
     * Get the middleware for the controller instance.
     *
     * @param  mixed  $controller
     * @param  string  $method
     * @return array
     */
    public static function getMiddleware($controller, $method)
    {
        if (! method_exists($controller, 'getMiddleware')) {
            return array();
        }

        $middleware = $controller->getMiddleware();

        return array_keys(array_filter($middleware, function ($options) use ($method)
        {
            return ! static::methodExcludedByOptions($method, $options);
        }));
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param  string  $method
     * @param  array  $options
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options)
    {
        if (isset($options['only']) && ! in_array($method, (array) $options['only'])) {
            return true;
        }

        return isset($options['except']) && in_array($method, (array) $options['except']);
    }
}
