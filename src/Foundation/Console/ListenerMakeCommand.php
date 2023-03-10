<?php

namespace Volcano\Foundation\Console;

use Volcano\Support\Str;
use Volcano\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ListenerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Event Listener class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Listener';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->option('event')) {
            return $this->error('Missing required option: --event');
        }

        parent::handle();
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $event = $this->option('event');

        //
        $namespace = $this->container->getNamespace();

        if (! Str::startsWith($event, $namespace)) {
            $event = $namespace .'Events\\' .$event;
        }

        $stub = str_replace(
            '{{event}}', class_basename($event), $stub
        );

        $stub = str_replace(
            '{{fullEvent}}', $event, $stub
        );

        return $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('queued')) {
            return realpath(__DIR__) .str_replace('/', DS, '/stubs/listener-queued.stub');
        } else {
            return realpath(__DIR__) .str_replace('/', DS, '/stubs/listener.stub');
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace .'\Listeners';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('event', 'e', InputOption::VALUE_REQUIRED, 'The event class being listened for.'),

            array('queued', null, InputOption::VALUE_NONE, 'Indicates the event listener should be queued.'),
        );
    }
}
