<?php

namespace Volcano\Filesystem;

use Volcano\Filesystem\Filesystem;

use Volcano\Support\ServiceProvider;


class FilesystemServiceProvider extends ServiceProvider
{

    /**
     * Enregistrez le fournisseur de services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('files', function()
        {
            return new Filesystem();
        });
    }

}
