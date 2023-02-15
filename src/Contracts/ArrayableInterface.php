<?php

namespace Volcano\Contracts;


interface ArrayableInterface
{
    
    /**
     * Obtenez l'instance sous forme de tableau.
     *
     * @return array
     */
    public function toArray();
}
