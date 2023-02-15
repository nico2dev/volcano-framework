<?php

namespace Volcano\Foundation\Console;

use Volcano\Console\Command;
use Volcano\Console\ConfirmableTrait;
use Volcano\Foundation\Publishers\ConfigPublisher;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class ConfigPublishCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'config:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Publish a package's configuration to the application";

    /**
     * The config publisher instance.
     *
     * @var \Volcano\Foundation\ConfigPublisher
     */
    protected $publisher;


    /**
     * Create a new configuration publish command instance.
     *
     * @param  \Volcano\Foundation\ConfigPublisher  $config
     * @return void
     */
    public function __construct(ConfigPublisher $publisher)
    {
        parent::__construct();

        $this->publisher = $publisher;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $package = $this->input->getArgument('package');

        $proceed = $this->confirmToProceed('Config Already Published!', function() use ($package)
        {
            return $this->publisher->alreadyPublished($package);
        });

        if (! $proceed) return;

        $this->publisher->publishPackage($package);

        $this->output->writeln('<info>Configuration published for package:</info> '.$package);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('package', InputArgument::REQUIRED, 'The configuration namespace of the package being published.'),
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
            array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when the file already exists.'),
        );
    }

}
