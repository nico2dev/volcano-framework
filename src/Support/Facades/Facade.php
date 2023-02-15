<?php

namespace Volcano\Support\Facades;


abstract class Facade
{

    /**
     * L'instance d'application étant en façade.
     *
     * @var \Volcano\Foundation\Application
     */
    protected static $app;

    /**
     * Les instances d'objets résolus.
     *
     * @var array
     */
    protected static $resolvedInstance;


    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Résolvez l'instance racine de la façade à partir du conteneur.
     *
     * @param  string  $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) return $name;

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$app[$name];
    }

    /**
     * Définissez l'instance de l'application.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @return void
     */
    public static function setFacadeApplication($app)
    {
        static::$app = $app;
    }

    /**
     * Obtenez l'instance de l'application.
     *
     * @return  \Volcano\Foundation\Application  $app
     */
    public static function getFacadeApplication()
    {
        return static::$app;
    }

    /**
     * Effacer une instance de façade résolue.
     *
     * @param  string  $name
     * @return void
     */
    public static function clearResolvedInstance($name)
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Effacez toutes les instances résolues.
     *
     * @return void
     */
    public static function clearResolvedInstances()
    {
        static::$resolvedInstance = array();
    }

    /**
     * Gérer les appels dynamiques et statiques à l'objet.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $accessor = static::getFacadeAccessor();

        $instance = static::resolveFacadeInstance($accessor);

        return call_user_func_array(array($instance, $method), $args);
    }

}
