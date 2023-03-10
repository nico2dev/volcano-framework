<?php

namespace Volcano\Http;

use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;


trait ResponseTrait
{
    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @param  bool    $replace
     * @return $this
     */
    public function header($key, $value, $replace = true)
    {
        $this->headers->set($key, $value, $replace);

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param  \Symfony\Component\HttpFoundation\Cookie  $cookie
     * @return $this
     */
    public function withCookie(SymfonyCookie $cookie)
    {
        $this->headers->setCookie($cookie);

        return $this;
    }

}
