<?php

namespace BrightMoon\Routing;

use Closure;

class RouteGroup
{
    protected $options = [
        'middleware' => [],
        'namespace' => '',
        'prefix' => '',
        'as' => '',
    ];

    protected $middleware = [];
    protected $namespace = '';
    protected $prefix = '';
    protected $as = '';

    protected $router;

    /**
     * Khởi tạo đối tượng.
     *
     * @param  \BrightMoon\Routing\Router  $router
     * @param  array  $options
     * @return void
     */
    public function __construct(Router $router, array $options)
    {
        $this->router = $router;
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Thực thi callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function execute(Closure $callback)
    {
        $this->router->setRouteGroupOptions($this->options);
        $callback();
        $this->router->setRouteGroupOptions();
    }

    /**
     * Xử lý gọi phương thức động của route group.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (property_exists($this, $method)) {
            $this->{$method} = $parameters[0];
            $this->options[$method] = $parameters[0];

            return $this;
        }

        throw new BrightMoonRouteException('Phương thức cho RouteGroup không hợp lệ.');
    }
}
