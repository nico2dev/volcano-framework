<?php

namespace Volcano\Foundation\Providers;

use Volcano\Foundation\Console\UpCommand;
use Volcano\Foundation\Console\DownCommand;
use Volcano\Foundation\Console\ServeCommand;
use Volcano\Foundation\Console\OptimizeCommand;
use Volcano\Foundation\Console\ModelMakeCommand;
use Volcano\Foundation\Console\ConsoleMakeCommand;
use Volcano\Foundation\Console\EnvironmentCommand;
use Volcano\Foundation\Console\EventMakeCommand;
use Volcano\Foundation\Console\JobMakeCommand;
use Volcano\Foundation\Console\KeyGenerateCommand;
use Volcano\Foundation\Console\ListenerMakeCommand;
use Volcano\Foundation\Console\PolicyMakeCommand;
use Volcano\Foundation\Console\ProviderMakeCommand;
use Volcano\Foundation\Console\RequestMakeCommand;
use Volcano\Foundation\Console\ClearCompiledCommand;
use Volcano\Foundation\Console\ClearLogCommand;
use Volcano\Foundation\Console\ViewClearCommand;
use Volcano\Foundation\Console\AssetLinkCommand;
use Volcano\Foundation\Console\SharedMakeCommand;

use Volcano\Foundation\Console\AssetPublishCommand;
use Volcano\Foundation\Console\ConfigPublishCommand;
use Volcano\Foundation\Console\VendorPublishCommand;
use Volcano\Foundation\Console\ViewPublishCommand;

use Volcano\Foundation\Publishers\AssetPublisher;
use Volcano\Foundation\Publishers\ConfigPublisher;
use Volcano\Foundation\Publishers\ViewPublisher;

use Volcano\Support\ServiceProvider;


class ForgeServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = array(
        'AssetPublish'     => 'command.asset.publish',
        'ConfigPublish'    => 'command.config.publish',
        'ClearCompiled'    => 'command.clear-compiled',
        'ClearLog'         => 'command.clear-log',
        'ConsoleMake'      => 'command.console.make',
        'Down'             => 'command.down',
        'Environment'      => 'command.environment',
        'EventMake'        => 'command.event.make',
        'JobMake'          => 'command.job.make',
        'KeyGenerate'      => 'command.key.generate',
        'ListenerMake'     => 'command.listener.make',
        'ModelMake'        => 'command.model.make',
        'Optimize'         => 'command.optimize',
        'PolicyMake'       => 'command.policy.make',
        'ProviderMake'     => 'command.provider.make',
        'RequestMake'      => 'command.request.make',
        'Serve'            => 'command.serve',
        'SharedMake'       => 'command.shared.make',
        'AssetLink'        => 'command.assets-link',
        'Up'               => 'command.up',
        'VendorPublish'    => 'command.vendor.publish',
        'ViewClear'        => 'command.view.clear',
        'ViewPublish'      => 'command.view.publish',
    );

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        foreach (array_keys($this->commands) as $command) {
            $method = "register{$command}Command";

            call_user_func_array(array($this, $method), array());
        }

        $this->commands(array_values($this->commands));
    }

    /**
     * Register the configuration publisher class and command.
     *
     * @return void
     */
    protected function registerAssetPublishCommand()
    {
        $this->app->singleton('asset.publisher', function($app)
        {
            $publisher = new AssetPublisher($app['files'], $app['path.public']);

            //
            $path = $app['path.base'] .DS .'vendor';

            $publisher->setPackagePath($path);

            return $publisher;
        });

        $this->app->singleton('command.asset.publish', function($app)
        {
            $assetPublisher  = $app['asset.publisher'];

            return new AssetPublishCommand($app['assets.dispatcher'], $assetPublisher);
        });
    }

    /**
     * Register the configuration publisher class and command.
     *
     * @return void
     */
    protected function registerConfigPublishCommand()
    {
        $this->app->singleton('config.publisher', function($app)
        {
            $path = $app['path'] .DS .'Config';

            $publisher = new ConfigPublisher($app['files'], $app['config'], $path);

            return $publisher;
        });

        $this->app->singleton('command.config.publish', function($app)
        {
            $configPublisher = $app['config.publisher'];

            return new ConfigPublishCommand($configPublisher);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerAssetLinkCommand()
    {
        $this->app->singleton('command.assets-link', function ()
        {
            return new AssetLinkCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerClearCompiledCommand()
    {
        $this->app->singleton('command.clear-compiled', function ()
        {
            return new ClearCompiledCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerClearLogCommand()
    {
        $this->app->singleton('command.clear-log', function ($app)
        {
            return new ClearLogCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerViewClearCommand()
    {
        $this->app->singleton('command.view.clear', function ($app)
        {
            return new ViewClearCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerConsoleMakeCommand()
    {
        $this->app->singleton('command.console.make', function ($app)
        {
            return new ConsoleMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerDownCommand()
    {
        $this->app->singleton('command.down', function ()
        {
            return new DownCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerEnvironmentCommand()
    {
        $this->app->singleton('command.environment', function ()
        {
            return new EnvironmentCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerEventMakeCommand()
    {
        $this->app->singleton('command.event.make', function ($app)
        {
            return new EventMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerJobMakeCommand()
    {
        $this->app->singleton('command.job.make', function ($app)
        {
            return new JobMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerKeyGenerateCommand()
    {
        $this->app->singleton('command.key.generate', function ($app)
        {
            return new KeyGenerateCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerListenerMakeCommand()
    {
        $this->app->singleton('command.listener.make', function ($app)
        {
            return new ListenerMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerModelMakeCommand()
    {
        $this->app->singleton('command.model.make', function ($app)
        {
            return new ModelMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerOptimizeCommand()
    {
        $this->app->singleton('command.optimize', function ($app)
        {
            return new OptimizeCommand($app['composer']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPolicyMakeCommand()
    {
        $this->app->singleton('command.policy.make', function ($app)
        {
            return new PolicyMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerProviderMakeCommand()
    {
        $this->app->singleton('command.provider.make', function ($app)
        {
            return new ProviderMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerRequestMakeCommand()
    {
        $this->app->singleton('command.request.make', function ($app)
        {
            return new RequestMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerServeCommand()
    {
        $this->app->singleton('command.serve', function ()
        {
            return new ServeCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerSharedMakeCommand()
    {
        $this->app->singleton('command.shared.make', function ($app)
        {
            return new SharedMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerUpCommand()
    {
        $this->app->singleton('command.up', function ()
        {
            return new UpCommand;
        });
    }

    /**
     * Register the vendor publish console command.
     *
     * @return void
     */
    protected function registerVendorPublishCommand()
    {
        $this->app->singleton('command.vendor.publish', function ($app)
        {
            return new VendorPublishCommand($app['files']);
        });
    }

    /**
     * Register the configuration publisher class and command.
     *
     * @return void
     */
    protected function registerViewPublishCommand()
    {
        $this->app->singleton('view.publisher', function($app)
        {
            $viewPath = $app['path'] .DS .'Views';

            $vendorPath = $app['path.base'] .DS .'vendor';

            //
            $publisher = new ViewPublisher($app['files'], $viewPath);

            $publisher->setPackagePath($vendorPath);

            return $publisher;
        });

        $this->app->singleton('command.view.publish', function($app)
        {
            return new ViewPublishCommand($app['packages'], $app['view.publisher']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }
}
