<?php

namespace Volcano\Routing\Matching;

use Volcano\Http\Request;

use Volcano\Routing\Route;


class HostValidator implements ValidatorInterface
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
        $regex = $route->getCompiled()->getHostRegex();

        if (is_null($regex)) return true;

        return preg_match($regex, $request->getHost());
    }

}
