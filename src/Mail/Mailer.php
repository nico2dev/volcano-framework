<?php

namespace Volcano\Mail;

use Volcano\Log\Writer;
use Volcano\View\Factory;
use Volcano\Events\Dispatcher;
use Volcano\Container\Container;
use Volcano\Queue\QueueManager;
use Volcano\Support\Arr;

use SuperClosure\Serializer;

use Swift_Mailer;
use Swift_Message;

use Closure;


class Mailer
{
    /**
     * The view factory instance.
     *
     * @var \Volcano\View\Factory
     */
    protected $views;

    /**
     * The Swift Mailer instance.
     *
     * @var \Swift_Mailer
     */
    protected $swift;

    /**
     * The event dispatcher instance.
     *
     * @var \Volcano\Events\Dispatcher
     */
    protected $events;

    /**
     * The global from address and name.
     *
     * @var array
     */
    protected $from;

    /**
     * The log writer instance.
     *
     * @var \Volcano\Log\Writer
     */
    protected $logger;

    /**
     * The IoC container instance.
     *
     * @var \Volcano\Container\Container
     */
    protected $container;

    /**
     * The QueueManager instance.
     *
     * @var \Volcano\Queue\QueueManager
     */
    protected $queue;

    /**
     * Indicates if the actual sending is disabled.
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * Array of failed recipients.
     *
     * @var array
     */
    protected $failedRecipients = array();

    /**
     * Array of parsed views containing html and text view name.
     *
     * @var array
     */
    protected $parsedViews = array();


    /**
     * Create a new Mailer instance.
     *
     * @param  \Volcano\View\Factory  $views
     * @param  \Swift_Mailer  $swift
     * @param  \Volcano\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Factory $views, Swift_Mailer $swift, Dispatcher $events = null)
    {
        $this->views = $views;
        $this->swift = $swift;
        $this->events = $events;
    }

    /**
     * Set the global from address and name.
     *
     * @param  string  $address
     * @param  string  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');
    }

    /**
     * Send a new message when only a raw text part.
     *
     * @param  string  $text
     * @param  mixed  $callback
     * @return int
     */
    public function raw($text, $callback)
    {
        return $this->send(array('raw' => $text), array(), $callback);
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  mixed   $callback
     * @return int
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(array('text' => $view), $data, $callback);
    }

    /**
     * Send a new message using a view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return void
     */
    public function send($view, array $data, $callback)
    {
        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        list($view, $plain, $raw) = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        $this->callMessageBuilder($callback, $message);

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $raw, $data);

        $message = $message->getSwiftMessage();

        $this->sendSwiftMessage($message);
    }

    /**
     * Queue a new e-mail message for sending.
     *
     * @param  string|array  $view
     * @param  array   $data
     * @param  \Closure|string  $callback
     * @param  string  $queue
     * @return mixed
     */
    public function queue($view, array $data, $callback, $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        return $this->queue->push('mailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * @param  string  $queue
     * @param  string|array  $view
     * @param  array   $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public function queueOn($queue, $view, array $data, $callback)
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds.
     *
     * @param  int  $delay
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $view, array $data, $callback, $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        return $this->queue->later($delay, 'mailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param  string  $queue
     * @param  int  $delay
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public function laterOn($queue, $delay, $view, array $data, $callback)
    {
        return $this->later($delay, $view, $data, $callback, $queue);
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param  mixed  $callback
     * @return mixed
     */
    protected function buildQueueCallable($callback)
    {
        if ( ! $callback instanceof Closure) return $callback;

        return (new Serializer)->serialize($callback);
    }

    /**
     * Handle a queued e-mail message job.
     *
     * @param  \Volcano\Queue\Jobs\Job  $job
     * @param  array  $data
     * @return void
     */
    public function handleQueuedMessage($job, $data)
    {
        $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

        $job->delete();
    }

    /**
     * Get the true callable for a queued e-mail message.
     *
     * @param  array  $data
     * @return mixed
     */
    protected function getQueuedCallable(array $data)
    {
        if (str_contains($data['callback'], 'SerializableClosure')) {
            return with(unserialize($data['callback']))->getClosure();
        }

        return $data['callback'];
    }

    /**
     * Add the content to a given message.
     *
     * @param  \Volcano\Mail\Message  $message
     * @param  string  $view
     * @param  string  $plain
     * @param  string  $raw
     * @param  array   $data
     * @return void
     */
    protected function addContent($message, $view, $plain, $raw, $data)
    {
        if (isset($view)) {
            $message->setBody($this->getView($view, $data), 'text/html');
        }

        if (isset($plain)) {
            $message->addPart($this->getView($plain, $data), 'text/plain');
        }

        if (isset($raw)) {
            $method = (isset($view) || isset($plain)) ? 'addPart' : 'setBody';

            call_user_func(array($message, $method), $raw, 'text/plain');
        }
    }

    /**
     * Parse the given view name or array.
     *
     * @param  string|array  $view
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseView($view)
    {
        if (is_string($view)) {
            return array($view, null, null);
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (is_array($view) && isset($view[0])) {
            return array($view[0], $view[1], null);
        }

        // If the view is an array, but doesn't contain numeric keys, we will assume
        // the the views are being explicitly specified and will extract them via
        // named keys instead, allowing the developers to use one or the other.
        else if (is_array($view)) {
            return array(
                Arr::get($view, 'html'),
                Arr::get($view, 'text'),
                Arr::get($view, 'raw'),
            );
        }

        throw new \InvalidArgumentException("Invalid view.");
    }

    /**
     * Send a Swift Message instance.
     *
     * @param  \Swift_Message  $message
     * @return void
     */
    protected function sendSwiftMessage($message)
    {
        if ($this->events) {
            $this->events->dispatch('mailer.sending', array($message));
        }

        if (! $this->pretending) {
            try {
                $this->swift->send($message, $this->failedRecipients);
            }
            finally {
                $this->swift->getTransport()->stop();
            }
        }

        // In the pretending mode.
        else if (isset($this->logger)) {
            $this->logMessage($message);
        }
    }

    /**
     * Log that a message was sent.
     *
     * @param  \Swift_Message  $message
     * @return void
     */
    protected function logMessage($message)
    {
        $emails = implode(', ', array_keys((array) $message->getTo()));

        $this->logger->info("Pretending to mail message to: {$emails}");
    }

    /**
     * Call the provided message builder.
     *
     * @param  \Closure|string  $callback
     * @param  \Volcano\Mail\Message  $message
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $message);
        } else if (is_string($callback)) {
            return $this->container[$callback]->mail($message);
        }

        throw new \InvalidArgumentException("Callback is not valid.");
    }

    /**
     * Create a new message instance.
     *
     * @return \Volcano\Mail\Message
     */
    protected function createMessage()
    {
        $message = new Message(new Swift_Message);

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }

    /**
     * Render the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @return \Volcano\View\View
     */
    protected function getView($view, $data)
    {
        return $this->views->make($view, $data)->render();
    }

    /**
     * Tell the mailer to not really send messages.
     *
     * @param  bool  $value
     * @return void
     */
    public function pretend($value = true)
    {
        $this->pretending = $value;
    }

    /**
     * Check if the mailer is pretending to send messages.
     *
     * @return bool
     */
    public function isPretending()
    {
        return $this->pretending;
    }

    /**
     * Get the view factory instance.
     *
     * @return \Volcano\View\Factory
     */
    public function getFactory()
    {
        return $this->views;
    }

    /**
     * Get the Swift Mailer instance.
     *
     * @return \Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->swift;
    }

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failedRecipients;
    }

    /**
     * Set the Swift Mailer instance.
     *
     * @param  \Swift_Mailer  $swift
     * @return void
     */
    public function setSwiftMailer($swift)
    {
        $this->swift = $swift;
    }

    /**
     * Set the log writer instance.
     *
     * @param  \Volcano\Log\Writer  $logger
     * @return $this
     */
    public function setLogger(Writer $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set the Queue Manager instance.
     *
     * @param  \Volcano\Queue\QueueManager  $queue
     * @return $this
     */
    public function setQueue(QueueManager $queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Volcano\Container\Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

}
