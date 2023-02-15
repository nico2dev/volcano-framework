<?php

namespace Volcano\Cache\Console;

use Volcano\Console\Command;
use Volcano\Cache\CacheManager;
use Volcano\Filesystem\Filesystem;


class ClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Flush the Application cache";

    /**
     * The Cache Manager instance.
     *
     * @var \Volcano\Cache\CacheManager
     */
    protected $cache;

    /**
     * The File System instance.
     *
     * @var \Volcano\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new Cache Clear Command instance.
     *
     * @param  \Volcano\Cache\CacheManager  $cache
     * @param  \Volcano\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(CacheManager $cache, Filesystem $files)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->cache->flush();

        $this->files->delete($this->container['config']['app.manifest'] .DS .'services.php');

        $this->info('Application cache cleared!');
    }

}
