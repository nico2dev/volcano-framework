<?php

namespace Volcano\Foundation\Exceptions;

use Volcano\Http\Request;


use Exception;


interface HandlerInterface
{

    /**
     * Signaler ou consigner une exception.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e);

    /**
     * Rendre une exception dans une réponse HTTP.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render(Request $request, Exception $e);

}
