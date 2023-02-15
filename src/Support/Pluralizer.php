<?php

namespace Volcano\Support;


use Doctrine\Inflector\InflectorFactory;


class Pluralizer
{
    /**
     * The cached inflector instance.
     *
     * @var static
     */
    protected static $inflector;
    
    /**
     * The language that should be used by the inflector.
     *
     * @var string
     */
    protected static $language = 'english';

    /**
     * Formes de mots indénombrables.
     *
     * @var array
     */
    public static $uncountable = array(
        'audio',
        'bison',
        'chassis',
        'compensation',
        'coreopsis',
        'data',
        'deer',
        'education',
        'equipment',
        'fish',
        'gold',
        'information',
        'knowledge',
        'love',
        'rain',
        'money',
        'moose',
        'nutrition',
        'offspring',
        'plankton',
        'police',
        'rice',
        'series',
        'sheep',
        'species',
        'swine',
        'traffic',
    );


    /**
     * Obtenez la forme plurielle d'un mot anglais.
     *
     * @param  string  $value
     * @param  int     $count
     * @return string
     */
    public static function plural($value, $count = 2)
    {
        if (($count === 1) || static::uncountable($value)) {
            return $value;
        }

        $plural = static::inflector()->pluralize($value);

        return static::matchCase($plural, $value);
    }

    /**
     * Obtenez la forme singulière d'un mot anglais.
     *
     * @param  string  $value
     * @return string
     */
    public static function singular($value)
    {
        $singular = static::inflector()->singularize($value);

        return static::matchCase($singular, $value);
    }

    /**
     * Déterminez si la valeur donnée est indénombrable.
     *
     * @param  string  $value
     * @return bool
     */
    protected static function uncountable($value)
    {
        return in_array(strtolower($value), static::$uncountable);
    }

    /**
     * Essayez de faire correspondre la casse sur deux chaînes.
     *
     * @param  string  $value
     * @param  string  $comparison
     * @return string
     */
    protected static function matchCase($value, $comparison)
    {
        $functions = array('mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords');

        foreach ($functions as $function) {
            if (call_user_func($function, $comparison) === $comparison) {
                return call_user_func($function, $value);
            }
        }

        return $value;
    }

    /**
     * Get the inflector instance.
     *
     * @return \Doctrine\Inflector\Inflector
     */
    public static function inflector()
    {
        if (is_null(static::$inflector)) {
            static::$inflector = InflectorFactory::createForLanguage(static::$language)->build();
        }

        return static::$inflector;
    }

}
