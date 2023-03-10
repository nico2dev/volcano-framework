<?php

namespace Volcano\Cache\Console;

use Volcano\Console\Command;
use Volcano\Cache\CacheManager;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class ForgetCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cache:forget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an item from the cache';

    /**
     * The cache manager instance.
     *
     * @var \Volcano\Cache\CacheManager
     */
    protected $cache;


    /**
     * Create a new cache clear command instance.
     *
     * @param  \Volcano\Cache\CacheManager  $cache
     * @return void
     */
    public function __construct(CacheManager $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->cache->driver($this->option('store'))->forget($key = $this->argument('key'));

        $this->info('The [' .$key .'] key has been removed from the cache.');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('key', InputArgument::REQUIRED, 'The key to remove'),
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
            array('store', null, InputOption::VALUE_OPTIONAL, 'The store to remove the key from.'),
        );
    }
}
