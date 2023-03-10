<?php

namespace Volcano\Routing;

use Volcano\Container\Container;

use Volcano\Http\Request;
use Volcano\Http\Exception\HttpResponseException;

use Volcano\Routing\Matching\HostValidator;
use Volcano\Routing\Matching\MethodValidator;
use Volcano\Routing\Matching\SchemeValidator;
use Volcano\Routing\Matching\UriValidator;
use Volcano\Routing\RouteCompiler;
use Volcano\Routing\RouteDependencyResolverTrait;

use Volcano\Support\Arr;
use Volcano\Support\Str;

//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Closure;
use ReflectionFunction;


class Route
{
    use RouteDependencyResolverTrait;

    /**
     * The container instance used by the route.
     *
     * @var \Volcano\Container\Container
     */
    protected $container;

    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $uri;

    /**
     * The HTTP methods the route responds to.
     *
     * @var array
     */
    protected $methods;

    /**
     * The route action array.
     *
     * @var array
     */
    protected $action;

    /**
     * Indicates whether the route is a fallback route.
     *
     * @var bool
     */
    protected $fallback = false;

    /**
     * The default values for the route.
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = array();

    /**
     * The array of matched parameters.
     *
     * @var array
     */
    protected $parameters;

    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    protected $parameterNames;

    /**
     * The compiled version of the route.
     *
     * @var \Symfony\Component\Routing\CompiledRoute
     */
    protected $compiled;

    /**
     * The computed gathered middleware.
     *
     * @var array|null
     */
    protected $computedMiddleware;

    /**
     * The Controller instance.
     *
     * @var mixed
     */
    protected $controllerInstance;

    /**
     * The Controller method.
     *
     * @var mixed
     */
    protected $controllerMethod;

    /**
     * The validators used by the routes.
     *
     * @var array
     */
    protected static $validators;

    protected $router;
    
    /**
     * Create a new Route instance.
     *
     * @param  array   $methods
     * @param  string  $uri
     * @param  \Closure|array  $action
     * @return void
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;

        $this->methods = (array) $methods;

        $this->action = $action;

        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        if (! is_null($prefix = Arr::get($this->action, 'prefix'))) {
            $this->prefix($prefix);
        }

        if (! is_null($fallback = Arr::get($this->action, 'fallback'))) {
            $this->fallback($fallback);
        }
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    public function run()
    {
        if (! isset($this->container)) {
            $this->container = new Container();
        }

        try {
            return $this->runActionCallback();
        }
        catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Runs the route action and returns the response.
     *
     * @return mixed
     */
    protected function runActionCallback()
    {
        if ($this->isControllerAction()) {
            return $this->runControllerAction();
        }

        $callback = Arr::get($this->action, 'uses');

        $parameters = $this->resolveMethodDependencies(
            $this->parametersWithoutNulls(), new ReflectionFunction($callback)
        );

        return call_user_func_array($callback, $parameters);
    }

    /**
     * Runs the route action and returns the response.
     *
     * @return mixed
     */
    protected function runControllerAction()
    {
        $dispatcher = new ControllerDispatcher($this->container);

        return $dispatcher->dispatch(
            $this, $this->getControllerInstance(), $this->getControllerMethod()
        );
    }

    /**
     * Checks whether the route's action is a controller.
     *
     * @return bool
     */
    protected function isControllerAction()
    {
        return is_string($this->action['uses']);
    }

    /**
     * Get the controller instance for the route.
     *
     * @return mixed
     */
    public function getControllerInstance()
    {
        if (isset($this->controllerInstance)) {
            return $this->controllerInstance;
        }

        $callback = Arr::get($this->action, 'uses');

        list ($controller, $this->controllerMethod) = Str::parseCallback($callback);

        return $this->controllerInstance = $this->container->make($controller);
    }

    /**
     * Get the controller method used for the route.
     *
     * @return string
     */
    public function getControllerMethod()
    {
        if (! isset($this->controllerMethod)) {
            $callback = Arr::get($this->action, 'uses');

            list (, $this->controllerMethod) = Str::parseCallback($callback);
        }

        return $this->controllerMethod;
    }

    /**
     * Determine if the route matches given request.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  bool  $includingMethod
     * @return bool
     */
    public function matches(Request $request, $includingMethod = true)
    {
        $this->compileRoute();

        $validators = array_filter($this->getValidators(), function ($validator) use ($includingMethod)
        {
            return ($validator instanceof MethodValidator) ? $includingMethod : true;
        });

        foreach ($validators as $validator) {
            if (! $validator->matches($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compile the route into a Symfony CompiledRoute instance.
     *
     * @return void
     */
    protected function compileRoute()
    {
        if (! is_null($this->compiled)) {
            return $this->compiled;
        }

        return $this->compiled = with(new RouteCompiler($this))->compile();
    }

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware()
    {
        if (! is_null($this->computedMiddleware)) {
            return $this->computedMiddleware;
        }

        $this->computedMiddleware = array();

        return $this->computedMiddleware = array_unique(array_merge(
            $this->middleware(), $this->controllerMiddleware()

        ), SORT_REGULAR);
    }

    /**
     * Get or set the middlewares attached to the route.
     *
     * @param  array|string|null $middleware
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return $this->getMiddleware();
        }

        if (is_string($middleware)) {
            $middleware = func_get_args();
        }

        $this->action['middleware'] = array_merge(
            $this->getMiddleware(), $middleware
        );

        return $this;
    }

    /**
     * Get the middlewares attached to the route.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return (array) Arr::get($this->action, 'middleware', array());
    }

    /**
     * Get the middleware for the route's controller.
     *
     * @return array
     */
    public function controllerMiddleware()
    {
        if (! $this->isControllerAction()) {
            return array();
        }

        return ControllerDispatcher::getMiddleware(
            $this->getControllerInstance(), $this->getControllerMethod()
        );
    }

    /**
     * Get a given parameter from the route.
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return string
     */
    public function getParameter($name, $default = null)
    {
        return $this->parameter($name, $default);
    }

    /**
     * Get a given parameter from the route.
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return string
     */
    public function parameter($name, $default = null)
    {
        return Arr::get($this->parameters(), $name, $default);
    }

    /**
     * Set a parameter to the given value.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @return void
     */
    public function setParameter($name, $value)
    {
        $this->parameters();

        $this->parameters[$name] = $value;
    }

    /**
     * Unset a parameter on the route if it is set.
     *
     * @param  string  $name
     * @return void
     */
    public function forgetParameter($name)
    {
        $this->parameters();

        unset($this->parameters[$name]);
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function parameters()
    {
        if (! isset($this->parameters)) {
            throw new \LogicException("The Route is not bound.");
        }

        return array_map(function ($value)
        {
            return is_string($value) ? rawurldecode($value) : $value;

        }, $this->parameters);
    }

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function parametersWithoutNulls()
    {
        return array_filter($this->parameters(), function ($parameter)
        {
            return ! is_null($parameter);
        });
    }

    /**
     * Get all of the parameter names for the route.
     *
     * @return array
     */
    public function parameterNames()
    {
        if (isset($this->parameterNames)) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParameterNames();
    }

    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    protected function compileParameterNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->domain() .$this->uri, $matches);

        return array_map(function ($match)
        {
            return trim($match, '?');

        }, $matches[1]);
    }

    /**
     * Bind the route to a given request for execution.
     *
     * @param  \Volcano\Http\Request  $request
     * @return $this
     */
    public function bind(Request $request)
    {
        $this->compileRoute();

        $this->bindParameters($request);

        return $this;
    }

    /**
     * Extract the parameter list from the request.
     *
     * @param  \Volcano\Http\Request  $request
     * @return array
     */
    public function bindParameters(Request $request)
    {
        // If the route has a regular expression for the host part of the URI, we will
        // compile that and get the parameter matches for this domain. We will then
        // merge them into this parameters array so that this array is completed.
        $parameters = $this->bindPathParameters($request);

        // If the route has a regular expression for the host part of the URI, we will
        // compile that and get the parameter matches for this domain. We will then
        // merge them into this parameters array so that this array is completed.
        if (! is_null($this->compiled->getHostRegex())) {
            $parameters = array_merge(
                $this->bindHostParameters($request), $parameters
            );
        }

        return $this->parameters = $this->replaceDefaults($parameters);
    }

    /**
     * Get the parameter matches for the path portion of the URI.
     *
     * @param  \Volcano\Http\Request  $request
     * @return array
     */
    protected function bindPathParameters(Request $request)
    {
        $regex = $this->compiled->getRegex();

        preg_match($regex, '/' .$request->decodedPath(), $matches);

        return $this->matchToKeys($matches);
    }

    /**
     * Extract the parameter list from the host part of the request.
     *
     * @param  \Volcano\Http\Request  $request
     * @return array
     */
    protected function bindHostParameters(Request $request)
    {
        $regex = $this->compiled->getHostRegex();

        preg_match($regex, $request->getHost(), $matches);

        return $this->matchToKeys($matches);
    }

    /**
     * Combine a set of parameter matches with the route's keys.
     *
     * @param  array  $matches
     * @return array
     */
    protected function matchToKeys(array $matches)
    {
        if (empty($parameterNames = $this->parameterNames())) {
            return array();
        }

        $parameters = array_intersect_key(
            $matches, array_flip($parameterNames)
        );

        return array_filter($parameters, function ($value)
        {
            return is_string($value) && (strlen($value) > 0);
        });
    }

    /**
     * Replace null parameters with their defaults.
     *
     * @param  array  $parameters
     * @return array
     */
    protected function replaceDefaults(array $parameters)
    {
        foreach ($parameters as $key => &$value) {
            if (! isset($value)) {
                $value = Arr::get($this->defaults, $key);
            }
        }

        return $parameters;
    }

    /**
     * Get the route validators for the instance.
     *
     * @return array
     */
    public static function getValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        // To match the route, we will use a chain of responsibility pattern with the
        // validator implementations. We will spin through each one making sure it
        // passes and then we will know if the route as a whole matches request.

        return static::$validators = array(
            new UriValidator(),
            new MethodValidator(),
            new SchemeValidator(),
            new HostValidator(),
        );
    }

    /**
     * Set a default value for the route.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function defaults($key, $value)
    {
        $this->defaults[$key] = $value;

        return $this;
    }

    /**
     * Get the regular expression requirements on the route.
     *
     * @return array
     */
    public function patterns()
    {
        return $this->wheres;
    }

    /**
     * Set a regular expression requirement on the route.
     *
     * @param  array|string  $name
     * @param  string  $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        $wheres = is_array($name) ? $name : array($name => $expression);

        foreach ($wheres as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * Returns true if the flag of fallback mode is set.
     *
     * @return bool
     */
    public function isFallback()
    {
        return $this->fallback;
    }

    /**
     * Set the flag of fallback mode on the route.
     *
     * @param  bool  $value
     * @return $this
     */
    public function fallback($value = true)
    {
        $this->fallback = (bool) $value;

        return $this;
    }

    /**
     * Get the URI associated with the route.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->uri();
    }

    /**
     * Get the URI associated with the route.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods();
    }

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function httpOnly()
    {
        return in_array('http', $this->action, true);
    }

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function httpsOnly()
    {
        return $this->secure();
    }

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function secure()
    {
        return in_array('https', $this->action, true);
    }

    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function domain()
    {
        return Arr::get($this->action, 'domain');
    }

    /**
     * Get the URI that the route responds to.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set the URI that the route responds to.
     *
     * @param  string  $uri
     * $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get the prefix of the route instance.
     *
     * @return string
     */
    public function getPrefix()
    {
        return Arr::get($this->action, 'prefix');
    }

    /**
     * Add a prefix to the route URI.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->uri = trim($prefix, '/') .'/' .trim($this->uri, '/');

        return $this;
    }

    /**
     * Get the name of the route instance.
     *
     * @return string
     */
    public function getName()
    {
        return Arr::get($this->action, 'as');
    }

    /**
     * Add or change the route name.
     *
     * @param  string  $name
     * @return $this
     */
    public function name($name)
    {
        if (! empty($namePrefix = Arr::get($this->action, 'as'))) {
            $name = $namePrefix .$name;
        }

        $this->action['as'] = $name;

        return $this;
    }

    /**
     * Get the action name for the route.
     *
     * @return string
     */
    public function getActionName()
    {
        return Arr::get($this->action, 'controller', 'Closure');
    }

    /**
     * Get the action array for the route.
     *
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the action array for the route.
     *
     * @param  array  $action
     * @return $this
     */
    public function setAction(array $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the compiled version of the route.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function getCompiled()
    {
        return $this->compiled;
    }

    /**
     * Set the container instance on the route.
     *
     * @param  \Volcano\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the router instance on the route.
     *
     * @param  \Volcano\Routing\Router  $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Dynamically access route parameters.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->parameter($key);
    }
}
