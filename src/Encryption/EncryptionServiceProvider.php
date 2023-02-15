<?php
/**
 * EncryptionServiceProvider - Implements a Service Provider for Encryption.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Volcano\Encryption;

use Volcano\Encryption\Encrypter;

use Volcano\Support\ServiceProvider;


class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('encrypter', function($app)
        {
            return new Encrypter($app['config']['app.key']);
        });
    }
}

