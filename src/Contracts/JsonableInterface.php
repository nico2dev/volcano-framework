<?php

namespace Volcano\Contracts;


interface JsonableInterface
{
    
    /**
     * Convertissez l'objet en sa représentation JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
