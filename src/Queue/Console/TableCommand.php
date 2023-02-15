<?php

namespace Volcano\Queue\Console;

use Volcano\Console\Command;
use Volcano\Foundation\Composer;
use Volcano\Filesystem\Filesystem;

use Volcano\Support\Str;


class TableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the queue jobs database table';

    /**
     * The filesystem instance.
     *
     * @var \Volcano\Filesystem\Filesystem
     */
    protected $files;


    /**
     * Create a new queue job table command instance.
     *
     * @param  \Volcano\Filesystem\Filesystem  $files
     * @param  \Volcano\Foundation\Composer    $composer
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
        $table = $this->container['config']['queue.connections.database.table'];

        $tableClassName = Str::studly($table);

        $fullPath = $this->createBaseMigration($table);

        $stubPath = __DIR__ .DS . 'stubs' .DS .'jobs.stub';

        $stub = str_replace(
            ['{{table}}', '{{tableClassName}}'], [$table, $tableClassName], $this->files->get($stubPath)
        );

        $this->files->put($fullPath, $stub);

        $this->info('Migration created successfully!');
    }

    /**
     * Create a base migration file for the table.
     *
     * @param  string  $table
     * @return string
     */
    protected function createBaseMigration($table = 'jobs')
    {
        $name = 'create_'.$table.'_table';

        $path = $this->container['path'] .DS .'Database' .DS .'Migrations';

        return $this->container['migration.creator']->create($name, $path);
    }
}
