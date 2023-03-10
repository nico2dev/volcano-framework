<?php

namespace Volcano\Packages\Console;

use Volcano\Console\Command;
use Volcano\Console\ConfirmableTrait;
use Volcano\Database\Migrations\Migrator;
use Volcano\Packages\Console\MigrationTrait;
use Volcano\Packages\PackageManager;
use Volcano\Support\Arr;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class PackageMigrateRollbackCommand extends Command
{
    use ConfirmableTrait;
    use MigrationTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'package:migrate:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the last database migrations for a specific or all Packages';

    /**
     * @var \Volcano\Packages\PackageManager
     */
    protected $packages;

    /**
     * @var Migrator
     */
    protected $migrator;


    /**
     * Create a new command instance.
     *
     * @param \Volcano\Packages\PackageManager $packages
     */
    public function __construct(Migrator $migrator, PackageManager $packages)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->packages = $packages;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $slug = $this->argument('slug');

        if (! empty($slug)) {
            if (! $this->packages->exists($slug)) {
                return $this->error('Package does not exist.');
            }

            return $this->rollback($slug);
        }

        foreach ($this->packages->all() as $package) {
            $this->comment('Rollback the last migration from Package: ' .$package['name']);

            $this->rollback($package['slug']);
        }
    }

    /**
     * Run the migration rollback for the specified Package.
     *
     * @param string $slug
     *
     * @return mixed
     */
    protected function rollback($slug)
    {
        if (! $this->packages->exists($slug)) {
            return $this->error('Package does not exist.');
        }

        $this->requireMigrations($slug);

        //
        $this->migrator->setConnection($this->input->getOption('database'));

        $pretend = $this->input->getOption('pretend');

        $this->migrator->rollback($pretend, $slug);

        //
        foreach ($this->migrator->getNotes() as $note) {
            if (! $this->option('quiet')) {
                $this->line($note);
            }
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
            array('slug', InputArgument::OPTIONAL, 'Package slug.'),
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
            array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
            array('force', null, InputOption::VALUE_NONE, 'Force the operation to run while in production.'),
            array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),
        );
    }
}
