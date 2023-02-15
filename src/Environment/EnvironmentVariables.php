<?php

namespace Volcano\Environment;

/**
 * Chargeur PHP $_ENV pour protéger les options de configuration sensibles.
 *
 * Inspiré de la merveilleuse bibliothèque "Dotenv" de Vance Lucas.
 */
class EnvironmentVariables
{

    /**
     * L'implémentation du chargeur d'environnement.
     *
     * @var \Volcano\Environment\EnvironmentLoaderInterface  $loader
     */
    protected $loader;


    /**
     * L'instance d'environnement de serveur.
     *
     * @param  \Volcano\Environment\EnvironmentLoaderInterface  $loader
     * @return void
     */
    public function __construct(EnvironmentVariablesLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Charger les variables du serveur pour un environnement donné.
     *
     * @param  string  $environment
     */
    public function load($environment = null)
    {
        foreach ($this->loader->load($environment) as $key => $value) {
            $_ENV[$key] = $value;

            $_SERVER[$key] = $value;

            putenv("{$key}={$value}");
        }
    }

}
