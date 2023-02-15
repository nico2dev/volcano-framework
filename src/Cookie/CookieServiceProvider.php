<?php
/**
 * CookieServiceProvider - Implements a Service Provider for CookieJar.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Volcano\Cookie;

use Volcano\Cookie\CookieJar;

use Volcano\Support\ServiceProvider;


class CookieServiceProvider extends ServiceProvider
{
    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cookie', function($app)
        {
            $config = $app['config']['session'];

            return with(new CookieJar())->setDefaultPathAndDomain($config['path'], $config['domain']);
        });
    }
}
