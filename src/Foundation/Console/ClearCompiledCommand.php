<?php

namespace Volcano\Foundation\Console;

use Volcano\Console\Command;


class ClearCompiledCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clear-compiled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove the compiled class file";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $config = $this->container['config'];

        if (file_exists($path = $config->get('app.manifest') .DS .'services.php')) {
            @unlink($path);
        }

        //$this->info('Compiled class file removed successfully!');
    }

}
