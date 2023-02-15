<?php

namespace Volcano\Contracts;


interface ResponsePreparerInterface
{
    
    /**
     * Préparez la valeur donnée en tant qu'objet Response.
     *
     * @param  mixed  $value
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareResponse($value);

    /**
     * Déterminez si le fournisseur est prêt à renvoyer des réponses.
     *
     * @return bool
     */
    public function readyForResponses();
}