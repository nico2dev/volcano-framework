<?php

namespace Volcano\Routing\Matching;

use Volcano\Http\Request;

use Volcano\Routing\Route;


class UriValidator implements ValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     *
     * @param  \Volcano\Routing\Route  $route
     * @param  \Volcano\Http\Request  $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        $regex = $route->getCompiled()->getRegex();

        $path = ($request->path() == '/') ? '/' : '/' .$request->path();

        return preg_match($regex, rawurldecode($path));
    }

}
