<?php

namespace Volcano\Foundation\Console;

use Volcano\Routing\Assets\AssetDispatcher;
use Volcano\Console\Command;
use Volcano\Foundation\Publishers\AssetPublisher;
use Volcano\Support\Str;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class AssetPublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'asset:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Publish a package's assets to the public directory";

    /**
     * The asset dispatcher instance.
     *
     * @var \Volcano\Asssets\AssetDispatcher
     */
    protected $dispatcher;

    /**
     * The asset publisher instance.
     *
     * @var \Volcano\Foundation\AssetPublisher
     */
    protected $publisher;


    /**
     * Create a new asset publish command instance.
     *
     * @param  \Volcano\Assets\AssetDispatcher $dispatcher
     * @param  \Volcano\Foundation\AssetPublisher  $assets
     * @return void
     */
    public function __construct(AssetDispatcher $dispatcher, AssetPublisher $publisher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;

        $this->publisher = $publisher;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->getPackages() as $package) {
            $this->publishAssets($package);
        }
    }

    /**
     * Publish the assets for a given package name.
     *
     * @param  string  $package
     * @return void
     */
    protected function publishAssets($package)
    {
        if (! $this->dispatcher->hasNamespace($package)) {
            return $this->error('Package does not exist.');
        }

        if ( ! is_null($path = $this->getPath())) {
            $this->publisher->publish($package, $path);
        } else {
            $path = $this->dispatcher->getPackagePath($package);

            $this->publisher->publishPackage($package, $path);
        }

        $this->output->writeln('<info>Assets published for package:</info> ' .$package);
    }

    /**
     * Get the name of the package being published.
     *
     * @return array
     */
    protected function getPackages()
    {
        if (! is_null($package = $this->input->getArgument('package'))) {
            if (Str::length($package) > 3) {
                $package = Str::snake($package, '-');
            } else {
                $package = Str::lower($package);
            }

            return array($package);
        }

        return $this->findAllAssetPackages();
    }

    /**
     * Find all the asset hosting packages in the system.
     *
     * @return array
     */
    protected function findAllAssetPackages()
    {
        $packages = array();

        //
        $namespaces = $this->dispatcher->getHints();

        foreach ($namespaces as $name => $hint) {
            $packages[] = $name;
        }

        return $packages;
    }

    /**
     * Get the specified path to the files.
     *
     * @return string
     */
    protected function getPath()
    {
        $path = $this->input->getOption('path');

        if (! is_null($path)) {
            return $this->container['path.base'] .DS .$path;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('package', InputArgument::OPTIONAL, 'The name of package being published.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('path', null, InputOption::VALUE_OPTIONAL, 'The path to the asset files.', null),
        );
    }
}
