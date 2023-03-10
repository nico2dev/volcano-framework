<?php

namespace Volcano\Routing;

use Volcano\Routing\Route;

use Symfony\Component\Routing\Route as SymfonyRoute;


class RouteCompiler
{
    /**
     * The route instance.
     *
     * @var \Volcano\Routing\Route
     */
    protected $route;

    /**
     * Create a new Route compiler instance.
     *
     * @param  \Volcano\Routing\Route  $route
     * @return void
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Compile the route.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function compile()
    {
        $route = $this->getRoute();

        if (empty($domain = $route->domain())) {
            $domain = '';
        }

        $optionals = $this->extractOptionalParameters($uri = $route->uri());

        $path = preg_replace('/\{(\w+?)\?\}/', '{$1}', $uri);

        return with(
            new SymfonyRoute($path, $optionals, $route->patterns(), array(), $domain)

        )->compile();
    }

    /**
     * Get the optional parameters for the route.
     *
     * @param string $uri
     *
     * @return array
     */
    protected function extractOptionalParameters($uri)
    {
        preg_match_all('/\{(\w+?)\?\}/', $uri, $matches);

        return isset($matches[1]) ? array_fill_keys($matches[1], null) : array();
    }

    /**
     * Get the inner Route instance.
     *
     * @return \Volcano\Routing\Route
     */
    public function getRoute()
    {
        return $this->route;
    }
}
