<?php

namespace Volcano\Console;

use Volcano\Console\Scheduling\Schedule;
use Volcano\Console\Scheduling\ScheduleRunCommand;
use Volcano\Console\Scheduling\ScheduleFinishCommand;

use Volcano\Support\ServiceProvider;


class ScheduleServiceProvider extends ServiceProvider
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
        $this->app->singleton('schedule', function ($app)
        {
            return new Schedule($app);
        });

        $this->registerScheduleRunCommand();
        $this->registerScheduleFinishCommand();
    }

    /**
     * Register the schedule run command.
     *
     * @return void
     */
    protected function registerScheduleRunCommand()
    {
        $this->app->singleton('command.schedule.run', function ($app)
        {
            return new ScheduleRunCommand($app['schedule']);
        });

        $this->commands('command.schedule.run');
    }

    /**
     * Register the schedule run command.
     *
     * @return void
     */
    protected function registerScheduleFinishCommand()
    {
        $this->app->singleton('command.schedule.finish', function ($app)
        {
            return new ScheduleFinishCommand($app['schedule']);
        });

        $this->commands('command.schedule.finish');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'command.schedule.run', 'command.schedule.finish'
        );
    }
}
