<?php
/**
 * DatabaseServiceProvider - Implements a Service Provider for Database.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Volcano\Database;

use Volcano\Database\ORM\Model;
use Volcano\Database\ConnectionFactory;
use Volcano\Database\DatabaseManager;
use Volcano\Support\ServiceProvider;


class DatabaseServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the Application events.
     *
     * @return void
     */
    public function boot()
    {
        $db = $this->app['db'];

        $events = $this->app['events'];

        // Setup the ORM Model.
        Model::setConnectionResolver($db);

        Model::setEventDispatcher($events);
    }

    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('db.factory', function($app)
        {
            return new ConnectionFactory($app);
        });

        $this->app->singleton('db', function($app)
        {
            return new DatabaseManager($app, $app['db.factory']);
        });
    }
}
