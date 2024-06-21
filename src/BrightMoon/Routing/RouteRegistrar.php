<?php

namespace BrightMoon\Routing;

use BrightMoon\Exceptions\BrightMoonRouteException;
use Closure;

class RouteRegistrar
{
    public array $middlewares = [];

    public ?string $namespace = null;

    public ?string $prefix = null;

    public function __construct(
        protected Router $router,
    ) {
    }

    public function middleware(array|string $middlewares): static
    {
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }

        $this->middlewares[] = array_merge($this->middlewares, $middlewares);

        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = ($this->prefix ?? '').'/'.ltrim($prefix, '/');

        return $this;
    }

    public function group($callback)
    {
        $options = [
            'prefix' => $this->prefix ?? '',
            'namespace' => $this->namespace ?? '',
            'middlewares' => $this->middlewares,
        ];

        if (is_string($callback)) {
            $this->router->routesRegistered[] = $callback;
            $this->router->updateGroupStack($options);
        } elseif ($callback instanceof Closure) {
            $this->router->group($options, $callback);
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
