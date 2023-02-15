<?php

namespace Volcano\Contracts;


interface RenderableInterface
{
    
    /**
     * Obtenez le contenu évalué de l'objet.
     *
     * @return string
     */
    public function render();
}
