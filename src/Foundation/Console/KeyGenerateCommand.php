<?php

namespace Volcano\Foundation\Console;

use Volcano\Support\Str;
use Volcano\Console\Command;
use Volcano\Filesystem\Filesystem;


class KeyGenerateCommand extends Command
{
    /**
     * The Console Command name.
     *
     * @var string
     */
    protected $name = 'key:generate';

    /**
     * The Console Command description.
     *
     * @var string
     */
    protected $description = "Set the Application Key";

    /**
     * 
     */
    protected $files;

    
    /**
     * Create a new Key Generator command.
     *
     * @param  \Volcano\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        list($path, $contents) = $this->getKeyFile();

        $key = $this->getRandomKey();

        $contents = str_replace($this->container['config']['app.key'], $key, $contents);

        $this->files->put($path, $contents);

        $this->container['config']['app.key'] = $key;

        $this->info("Application key [$key] set successfully.");
    }

    /**
     * Get the key file and contents.
     *
     * @return array
     */
    protected function getKeyFile()
    {
        $path = $this->container['path'] .DS .'Config' .DS .'App.php';

        $contents = $this->files->get($path);

        return array($path, $contents);
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function getRandomKey()
    {
        return Str::random(32);
    }

}
