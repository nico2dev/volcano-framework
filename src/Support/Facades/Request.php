<?php

namespace Volcano\Support\Facades;

use Volcano\Http\Request as HttpRequest;
use Volcano\Support\Facades\Facade;

use ReflectionMethod;
use ReflectionException;


/**
 * @see \Volcano\Http\Request
 */
class Request extends Facade
{

    /**
     * Renvoie l'instance Application.
     *
     * @return \Volcano\Http\Request
     */
    public static function instance()
    {
        $accessor = static::getFacadeAccessor();

        return static::resolveFacadeInstance($accessor);
    }

    /**
     * Magic Method pour appeler les méthodes sur l'instance Request par défaut.
     *
     * @param $method
     * @param $params
     *
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        // Gérez d'abord les méthodes statiques de HttpRequest.
        try {
            $reflection = new ReflectionMethod(HttpRequest::class, $method);

            if ($reflection->isStatic()) {
                // La méthode demandée est statique.
                return call_user_func_array(array(HttpRequest::class, $method), $params);
            }
        } catch (ReflectionException $e) {
            // Méthode introuvable ne fais rien.
        }

        // Obtenez une instance HttpRequest.
        $instance = static::instance();

        // Prend en charge la vérification de la méthode HTTP via isX.
        if (starts_with($method, 'is') && (strlen($method) > 4)) {
            return ($instance->method() == strtoupper(substr($method, 2)));
        }

        // Appelez la méthode non statique à partir de l'instance Request.
        return call_user_func_array(array($instance, $method), $params);
    }

    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'request'; }

}
