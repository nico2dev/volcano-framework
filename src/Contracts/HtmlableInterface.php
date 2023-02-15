<?php

namespace Volcano\Contracts;


interface HtmlableInterface
{
    
    /**
     * Obtenez le contenu sous forme de chaîne HTML.
     *
     * @return string
     */
    public function toHtml();
}
