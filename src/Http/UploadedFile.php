<?php

namespace Volcano\Http;

use Volcano\Support\Traits\MacroableTrait;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;


class UploadedFile extends SymfonyUploadedFile
{
    use MacroableTrait;

    /**
     * Get the fully qualified path to the file.
     *
     * @return string
     */
    public function path()
    {
        return $this->getRealPath();
    }

    /**
     * Get the file's extension.
     *
     * @return string
     */
    public function extension()
    {
        return $this->guessExtension();
    }

    /**
     * Get the file's extension supplied by the client.
     *
     * @return string
     */
    public function clientExtension()
    {
        return $this->guessClientExtension();
    }

    /**
     * Get a filename for the file that is the MD5 hash of the contents.
     *
     * @param  string  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if (! is_null($path)) {
            $path = rtrim($path, '/\\') .DS;
        }

        return $path .md5_file($this->path()) .'.' .$this->extension();
    }

    /**
     * Create a new file instance from a base instance.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @param  bool $test
     * @return static
     */
    public static function createFromBase(SymfonyUploadedFile $file, $test = false)
    {
        return ($file instanceof static) ? $file : new static(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            //$file->getClientSize(),
            $file->getError(),
            $test
        );
    }
}
