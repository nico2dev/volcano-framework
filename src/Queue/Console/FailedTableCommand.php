<?php

namespace Volcano\Queue\Console;

use Volcano\Console\Command;
use Volcano\Filesystem\Filesystem;


class FailedTableCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:failed-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the failed queue jobs database table';

    /**
     * The filesystem instance.
     *
     * @var \Volcano\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new session table command instance.
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
        $fullPath = $this->createBaseMigration();

        $stubPath = __DIR__ .DS .'stubs' .DS .'failed_jobs.stub';

        $this->files->put($fullPath, $this->files->get($stubPath));

        $this->info('Migration created successfully!');
    }

    /**
     * Create a base migration file for the table.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'create_failed_jobs_table';

        $path = $this->container['path'] .DS .'Database' .DS .'Migrations';

        return $this->container['migration.creator']->create($name, $path);
    }

}