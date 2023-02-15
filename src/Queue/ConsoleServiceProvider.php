<?php

namespace Volcano\Queue;

use Volcano\Queue\Console\RetryCommand;
use Volcano\Queue\Console\ListFailedCommand;
use Volcano\Queue\Console\FlushFailedCommand;
use Volcano\Queue\Console\FailedTableCommand;
use Volcano\Queue\Console\ForgetFailedCommand;
use Volcano\Queue\Console\TableCommand;
//use Volcano\Queue\Console\AsyncCommand;

use Volcano\Support\ServiceProvider;


class ConsoleServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.queue.table', function ($app) {
            return new TableCommand($app['files']);
        });

        $this->app->singleton('command.queue.failed', function()
        {
            return new ListFailedCommand;
        });

        $this->app->singleton('command.queue.retry', function()
        {
            return new RetryCommand;
        });

        $this->app->singleton('command.queue.forget', function()
        {
            return new ForgetFailedCommand;
        });

        $this->app->singleton('command.queue.flush', function()
        {
            return new FlushFailedCommand;
        });

        $this->app->singleton('command.queue.failed-table', function($app)
        {
            return new FailedTableCommand($app['files']);
        });

        $this->commands(
            'command.queue.table', 'command.queue.failed', 'command.queue.retry',
            'command.queue.forget', 'command.queue.flush', 'command.queue.failed-table'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'command.queue.table', 'command.queue.failed', 'command.queue.retry',
            'command.queue.forget', 'command.queue.flush', 'command.queue.failed-table',
        );
    }

}
