<?php

namespace Volcano\Foundation\Bus;

use Volcano\Bus\DispatcherInterface as Dispatcher;

use Volcano\Support\Facades\App;


trait DispatchesJobsTrait
{

    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return mixed
     */
    protected function dispatch($job)
    {
        $dispatcher = App::make(Dispatcher::class);

        return $dispatcher->dispatch($job);
    }

    /**
     * Dispatch a job to its appropriate handler in the current process.
     *
     * @param  mixed  $job
     * @return mixed
     */
    public function dispatchNow($job)
    {
        $dispatcher = App::make(Dispatcher::class);

        return $dispatcher->dispatchNow($job);
    }
}
