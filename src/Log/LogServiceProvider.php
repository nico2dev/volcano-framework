<?php

namespace Volcano\Log;

use Volcano\Log\Writer;

use Volcano\Support\ServiceProvider;

use Monolog\Logger as Monolog;


class LogServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('log', function ()
        {
            return $this->createLogger();
        });
    }

    /**
     * Create the logger.
     *
     * @return \Volcano\Log\Writer
     */
    protected function createLogger()
    {
        $log = new Writer(
            new Monolog('Volcano'), $this->app['events']
        );

        $this->configureHandler($log);

        return $log;
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @param  \Volcano\Log\Writer  $log
     * @return void
     */
    protected function configureHandler(Writer $log)
    {
        $driver = $this->app['config']['app.log'];

        $method = 'configure' .ucfirst($driver) .'Handler';

        call_user_func(array($this, $method), $log);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @param  \Volcano\Log\Writer  $log
     * @return void
     */
    protected function configureSingleHandler(Writer $log)
    {
        $log->useFiles($this->app['path.storage'] .DS .'logs' .DS .'framework.log');
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Volcano\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles(
            $this->app['path.storage'] .DS .'logs' .DS .'framework.log',
            $this->app['config']->get('app.log_max_files', 5)
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Volcano\Log\Writer  $log
     * @return void
     */
    protected function configureSyslogHandler(Writer $log)
    {
        $log->useSyslog('Volcano');
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Volcano\Log\Writer  $log
     * @return void
     */
    protected function configureErrorlogHandler(Writer $log)
    {
        $log->useErrorLog();
    }
}
