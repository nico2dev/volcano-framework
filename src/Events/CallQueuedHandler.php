<?php

namespace Volcano\Events;

use Volcano\Queue\Job;

use Volcano\Container\Container;


class CallQueuedHandler
{
    
    /**
     * L'instance de conteneur.
     *
     * @var \Volcano\Container\Container
     */
    protected $container;


    /**
     * Créer une nouvelle instance de travail.
     *
     * @param  \Volcano\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Gérer le travail en file d'attente.
     *
     * @param  \Volcano\Queue\Job  $job
     * @param  array  $data
     * @return void
     */
    public function call(Job $job, array $data)
    {
        $handler = $this->setJobInstanceIfNecessary(
            $job, $this->container->make($data['class'])
        );

        call_user_func_array(
            array($handler, $data['method']), unserialize($data['data'])
        );

        if (! $job->isDeletedOrReleased()) {
            $job->delete();
        }
    }

    /**
     * Définissez l'instance de travail de la classe donnée si nécessaire.
     *
     * @param  \Volcano\Queue\Job  $job
     * @param  mixed  $instance
     * @return mixed
     */
    protected function setJobInstanceIfNecessary(Job $job, $instance)
    {
        $traits = class_uses_recursive(get_class($instance));

        if (in_array('Volcano\Queue\InteractsWithQueueTrait', $traits)) {
            $instance->setJob($job);
        }

        return $instance;
    }

    /**
     * Appelez la méthode ayant échoué sur l'instance de travail.
     *
     * @param  array  $data
     * @return void
     */
    public function failed(array $data)
    {
        $handler = $this->container->make($data['class']);

        if (method_exists($handler, 'failed')) {
            call_user_func_array(array($handler, 'failed'), unserialize($data['data']));
        }
    }
}
