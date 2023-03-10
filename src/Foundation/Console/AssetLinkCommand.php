<?php

namespace Volcano\Foundation\Console;

use Volcano\Console\Command;


class AssetLinkCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'asset:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "webroot/assets" to the "assets" folder';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (file_exists($publicPath = public_path('assets'))) {
            return $this->error('The "webroot/assets" directory already exists.');
        }

        $assetsPath = $this->container['config']->get('routing.assets.path', base_path('assets'));

        $this->container->make('files')->link(
            $assetsPath, $publicPath
        );

        $this->info('The [webroot/assets] directory has been linked.');
    }
}
