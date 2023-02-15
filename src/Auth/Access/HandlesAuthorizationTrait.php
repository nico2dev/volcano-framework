<?php

namespace Volcano\Auth\Access;

use Volcano\Auth\Access\Response;
use Volcano\Auth\Access\UnAuthorizedException;


trait HandlesAuthorizationTrait
{
    /**
     * Create a new access response.
     *
     * @param  string|null  $message
     * @return \Volcano\Auth\Access\Response
     */
    protected function allow($message = null)
    {
        return new Response($message);
    }

    /**
     * Throws an unauthorized exception.
     *
     * @param  string  $message
     * @return void
     *
     * @throws \Volcano\Auth\Access\UnauthorizedException
     */
    protected function deny($message = null)
    {
        $message = $message ?: __d('volcano', 'This action is unauthorized.');

        throw new UnAuthorizedException($message);
    }
}
