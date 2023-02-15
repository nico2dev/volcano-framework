<?php

namespace Volcano\Routing\Matching;

use Volcano\Http\Request;

use Volcano\Routing\Route;


class SchemeValidator implements ValidatorInterface
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
        if ($route->httpOnly()) {
            return ! $request->secure();
        } else if ($route->secure()) {
            return $request->secure();
        }

        return true;
    }

}
