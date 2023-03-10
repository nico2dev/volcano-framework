<?php

namespace Volcano\Exception;

use Volcano\Exception\PlainDisplayer; // Not used
use Volcano\Exception\WhoopsDisplayer; // Not used
use Volcano\Exception\ExceptionDisplayerInterface; // Not used

use Volcano\Foundation\Application;

//use Volcano\Contracts\ResponsePreparerInterface;
//use Volcano\Support\Facades\Redirect;

use Symfony\Component\Console\Output\ConsoleOutput;
//use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Volcano\Debug\Exception\FatalErrorException;
use Volcano\Debug\Exception\FatalThrowableError;

use Closure;
use Exception;
use ErrorException;
use ReflectionFunction;


class Handler
{
    /**
     * The response preparer implementation.
     *
     * @var \Volcano\Foundation\Application
     */
    protected $app;

    /**
     * Indicates if the application is in debug mode.
     *
     * @var bool
     */
    protected $debug;


    /**
     * Create a new error handler instance.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @param  bool  $debug
     * @return void
     */
    public function __construct(Application $app,  $debug = true)
    {
        $this->app = $app;

        $this->debug = $debug;
    }

    /**
     * Register the exception / error handlers for the application.
     *
     * @param  string  $environment
     * @return void
     */
    public function register($environment)
    {
        set_error_handler(array($this, 'handleError'));

        set_exception_handler(array($this, 'handleException'));

        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * Handle a PHP error for the application.
     *
     * @param  int     $level
     * @param  string  $message
     * @param  string  $file
     * @param  int     $line
     * @param  array   $context
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an exception for the application.
     *
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleException($exception)
    {
        if (! $exception instanceof Exception) {
            $exception = new FatalThrowableError($exception);
        }

        $this->getExceptionHandler()->report($exception);

        if ($this->app->runningInConsole()) {
            $this->renderForConsole($exception);
        } else {
            $this->renderHttpResponse($exception);
        }
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int   $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE));
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param  array  $error
     * @param  int|null  $traceOffset
     * @return \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Exception  $e
     * @return void
     */
    protected function renderForConsole($e, ConsoleOutput $output = null)
    {
        if (is_null($output)) {
            $output = new ConsoleOutput();
        }

        $this->getExceptionHandler()->renderForConsole($output, $e);
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param  \Exception  $e
     * @return void
     */
    protected function renderHttpResponse($e)
    {
        $this->getExceptionHandler()->render($this->app['request'], $e)->send();
    }

    /**
     * Set the debug level for the handler.
     *
     * @param  bool  $debug
     * @return void
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Get an instance of the exception handler.
     *
     * @return \Volcano\Foundation\Exceptions\HandlerInterface
     */
    protected function getExceptionHandler()
    {
        return $this->app->make('Volcano\Foundation\Exceptions\HandlerInterface');
    }
}
