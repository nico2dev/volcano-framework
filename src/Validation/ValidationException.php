<?php

namespace Volcano\Validation;

use Volcano\Contracts\MessageProviderInterface;

use RuntimeException;


class ValidationException extends RuntimeException
{
    /**
     * The message provider implementation.
     *
     * @var \Volcano\Support\Contracts\MessageProviderInterface
     */
    protected $provider;

    /**
     * Create a new validation exception instance.
     *
     * @param  \Volcano\Support\MessageProvider  $provider
     * @return void
     */
    public function __construct(MessageProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the validation error message provider.
     *
     * @return \Volcano\Support\MessageBag
     */
    public function errors()
    {
        return $this->provider->getMessageBag();
    }

    /**
     * Get the validation error message provider.
     *
     * @return \Volcano\Support\MessageProviderInterface
     */
    public function getMessageProvider()
    {
        return $this->provider;
    }
}
