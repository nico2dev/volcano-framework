<?php

namespace Volcano\Foundation;


class AliasLoader
{

    /**
     * Le tableau des alias de classe.
     *
     * @var array
     */
    protected $aliases;

    /**
     * Indique si un chargeur a été enregistré.
     *
     * @var bool
     */
    protected $registered = false;

    /**
     * L'instance singleton du chargeur.
     *
     * @var \Volcano\Foundation\AliasLoader
     */
    protected static $instance;


    /**
     * Créez une nouvelle instance de chargeur d'alias de classe.
     *
     * @param  array  $aliases
     * @return void
     */
    public function __construct(array $aliases = array())
    {
        $this->aliases = $aliases;
    }

    /**
     * Obtenez ou créez l'instance de chargeur d'alias singleton.
     *
     * @param  array  $aliases
     * @return \Volcano\Foundation\AliasLoader
     */
    public static function getInstance(array $aliases = array())
    {
        if (is_null(static::$instance)) return static::$instance = new static($aliases);

        $aliases = array_merge(static::$instance->getAliases(), $aliases);

        static::$instance->setAliases($aliases);

        return static::$instance;
    }

    /**
     * Charger un alias de classe s'il est enregistré.
     *
     * @param  string  $alias
     * @return void
     */
    public function load($alias)
    {
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }
    }

    /**
     * Ajouter un alias au chargeur.
     *
     * @param  string  $class
     * @param  string  $alias
     * @return void
     */
    public function alias($class, $alias)
    {
        $this->aliases[$class] = $alias;
    }

    /**
     * Enregistrez le chargeur sur la pile de chargeurs automatiques.
     *
     * @return void
     */
    public function register()
    {
        if (! $this->registered) {
            $this->prependToLoaderStack();

            $this->registered = true;
        }
    }

    /**
     * Ajoutez la méthode de chargement à la pile du chargeur automatique.
     *
     * @return void
     */
    protected function prependToLoaderStack()
    {
        spl_autoload_register(array($this, 'load'), true, true);
    }

    /**
     * Obtenez les alias enregistrés.
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Définissez les alias enregistrés.
     *
     * @param  array  $aliases
     * @return void
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * Indique si le chargeur a été enregistré.
     *
     * @return bool
     */
    public function isRegistered()
    {
        return $this->registered;
    }

    /**
     * Définissez l'état "enregistré" du chargeur.
     *
     * @param  bool  $value
     * @return void
     */
    public function setRegistered($value)
    {
        $this->registered = $value;
    }

    /**
     * Définissez la valeur du chargeur d'alias singleton.
     *
     * @param  \Volcano\Foundation\AliasLoader  $loader
     * @return void
     */
    public static function setInstance($loader)
    {
        static::$instance = $loader;
    }

}
