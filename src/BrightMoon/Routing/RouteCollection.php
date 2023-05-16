<?php

namespace BrightMoon\Routing;

class RouteCollection
{
    protected $registered = [];

    /**
     * Danh sách các options của route group.
     *
     * @var array
     */
    protected $routeGroupOptions = [
        'middleware' => '',
        'namespace' => '',
        'prefix' => '',
        'as' => '',
    ];

    /**
     * Thêm route vào danhs sách.
     *
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function addRoute($methods, $uri, $action)
    {
        $route = new Route($methods, $uri, $action);
        $route->configRoute($this->routeGroupOptions);

        foreach ($methods as $method) {
            $this->registered[$method][] = $route;
        }

        return $route;
    }
}
