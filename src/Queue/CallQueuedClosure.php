<?php

use Volcano\Encryption\Encrypter;
use Volcano\Queue\Job;


class CallQueuedClosure
{
    /**
     * The encrypter instance.
     *
     * @var \Volcano\Encryption\Encrypter  $crypt
     */
    protected $crypt;


    /**
     * Create a new queued Closure job.
     *
     * @param  \Volcano\Encryption\Encrypter  $crypt
     * @return void
     */
    public function __construct(Encrypter $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Fire the Closure based queue job.
     *
     * @param  \Volcano\Queue\Job  $job
     * @param  array  $data
     * @return void
     */
    public function call(Job $job, array $data)
    {
        $payload = $this->crypt->decrypt(
            $data['closure']
        );

        $closure = unserialize($payload);

        call_user_func($closure, $job);
    }
}
