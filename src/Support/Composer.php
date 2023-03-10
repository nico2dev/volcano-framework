<?php

namespace Volcano\Support;

use Volcano\Filesystem\Filesystem;
use Volcano\Support\ProcessUtils;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;


class Composer
{
    /**
     * The filesystem instance.
     *
     * @var \Volcano\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The working path to regenerate from.
     *
     * @var string
     */
    protected $workingPath;


    /**
     * Create a new Composer manager instance.
     *
     * @param  \Volcano\Filesystem\Filesystem  $files
     * @param  string  $workingPath
     * @return void
     */
    public function __construct(Filesystem $files, $workingPath = null)
    {
        $this->files = $files;

        $this->workingPath = $workingPath;
    }

    /**
     * Regenerate the Composer autoloader files.
     *
     * @param  string  $extra
     * @return void
     */
    public function dumpAutoloads($extra = '')
    {
        $extra = $extra ? (array) $extra : [];

        $command = $this->findComposer() .' dump-autoload';

        if (! empty($extra)) {
            $command = trim($command .' ' .$extra);
        }

        return $this->getProcess($command)->run();

        //$process->setCommandLine($command);

        //$process->run();
    }

    /**
     * Regenerate the optimized Composer autoloader files.
     *
     * @return void
     */
    public function dumpOptimized()
    {
        $this->dumpAutoloads('--optimize');
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPhar = $this->workingPath .DS .'composer.phar';

        if ($this->files->exists($composerPhar)) {
            $executable = with(new PhpExecutableFinder)->find(false);

            return ProcessUtils::escapeArgument($executable) .' ' .$composerPhar;
        }

        return 'composer';
    }

    /**
     * Get a new Symfony process instance.
     *
     * @return \Symfony\Component\Process\Process
     */
    //protected function getProcess()
    protected function getProcess($command)
    {
        //$process = new Process('', $this->workingPath);
        $process = new Process($command, $this->workingPath);

        return $process->setTimeout(null);
    }

    /**
     * Set the working path used by the class.
     *
     * @param  string  $path
     * @return $this
     */
    public function setWorkingPath($path)
    {
        $this->workingPath = realpath($path);

        return $this;
    }

}
