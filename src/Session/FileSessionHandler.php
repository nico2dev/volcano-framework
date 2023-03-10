<?php

namespace Volcano\Session;

use Volcano\Filesystem\Filesystem;

use Symfony\Component\Finder\Finder;

use Carbon\Carbon;


class FileSessionHandler implements \SessionHandlerInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Volcano\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new file driven handler instance.
     *
     * @param  \Volcano\Filesystem\Filesystem  $files
     * @param  string  $path
     * @param  int  $minutes
     * @return void
     */
    public function __construct(Filesystem $files, $path, $minutes)
    {
        $this->path    = $path;
        $this->files   = $files;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId): string
    {
        if ($this->files->exists($path = $this->path .DS .$sessionId)) {
            if (filemtime($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
                return $this->files->get($path, true);
            }
        }

        return '';
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange] 
    public function write($sessionId, $data)
    {
        $this->files->put($this->path .DS .$sessionId, $data, true);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function destroy($sessionId)
    {
        $this->files->delete($this->path .DS .$sessionId);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function gc($lifetime)
    {
        $files = Finder::create()
                    ->in($this->path)
                    ->files()
                    ->ignoreDotFiles(true)
                    ->date('<= now - ' .$lifetime .' seconds');

        foreach ($files as $file) {
            $this->files->delete($file->getRealPath());
        }
    }

}
