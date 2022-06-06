<?php

namespace BrightMoon\Routing;

use BrightMoon\Exceptions\BrightMoonRouteException;
use Closure;

class RouteRegistrar
{
    /**
     * @var array
     */
    public $middleware = [];

    /**
     * @var string
     */
    public $namespace = '';

    /**
     * @var string
     */
    public $prefix = '';

    /**
     * @var \BrightMoon\Routing\Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function middleware($middlewares)
    {
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }

        $this->middleware[] = $middlewares;

        return $this;
    }

    public function group($callback)
    {
        $options = [
            'prefix' => $this->prefix,
            'namespace' => $this->namespace,
            'middleware' => $this->middleware,
        ];

        if (is_string($callback)) {
            $this->router->routesRegistered[] = $callback;
            $this->router->setRouteGroupOptions($options);
        } elseif ($callback instanceof Closure) {
            $routeGroup = new RouteGroup($this->router, $options);
            $routeGroup->execute($callback);
        }
    }

    public function __call($method, $parameters)
    {
        if (property_exists($this, $method)) {
            $this->{$method} = $parameters[0];

            return $this;
        }

        throw new BrightMoonRouteException('Phương thức cho RouteRegistrar không hợp lệ.');
    }
}
