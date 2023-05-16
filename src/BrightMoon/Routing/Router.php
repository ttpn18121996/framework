<?php

namespace BrightMoon\Routing;

use BrightMoon\Http\Request;
use BrightMoon\Http\Response;
use BrightMoon\Support\Str;
use BrightMoon\Exceptions\BrightMoonRouteException;
use ReflectionFunction;
use Closure;

class Router
{
    /**
     * Danh sách Route đã đăng ký.
     *
     * @var array
     */
    public $routes = [];

    /**
     * Các phương thức truyền cho router.
     *
     * @var array
     */
    public $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', ];

    /**
     * Thiết lập route mặc định không cần định nghĩa (controller/action/parameter).
     *
     * @var bool
     */
    public $routeDefault = false;

    /**
     * Danh sách các routes đăng ký trong provider.
     *
     * @var array
     */
    public $routesRegistered = [];

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
     * Thiết lập route với phương thức GET.
     *
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return \BrightMoon\RoutingRoute
     */
    public function get($uri, $action)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức POST.
     *
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return \BrightMoon\RoutingRoute
     */
    public function post($uri, $action)
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức PUT.
     *
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function put($uri, $action)
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức PATCH.
     *
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function patch($uri, $action)
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức DELETE.
     *
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function delete($uri, $action)
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức OPTIONS.
     *
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function options($uri, $action)
    {
        return $this->addRoute(['OPTIONS'], $uri, $action);
    }

    /**
     * Thiết lập route với mọi phương thức.
     *
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function any($uri, $action)
    {
        return $this->addRoute($this->verbs, $uri, $action);
    }

    /**
     * Thiết lập route với phương thức được định nghĩa cụ thể.
     *
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string $action
     * @return \BrightMoon\RoutingRoute
     */
    public function match($methods, $uri, $action)
    {
        return $this->addRoute((array) $methods, $uri, $action);
    }

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
            $this->routes[$method][] = $route;
        }

        return $route;
    }

    /**
     * Thực hiện điều hướng.
     *
     * @param  \BrightMoon\Http\Request  $request
     * @return mixed
     */
    public function run(Request $request)
    {
        $controller = null;
        $action = null;
        $parameters = null;
        $found = false;
        $uri = $this->getBaseUri();
        $httpRequestMethod = $request->method();

        if (in_array($httpRequestMethod, $this->verbs) && isset($this->routes[$httpRequestMethod])) {
            foreach ($this->routes[$httpRequestMethod] as $route) {
                if ($route->compare($uri)) {
                    $controller = $route->getController();
                    $action = $route->action;
                    $parameters = $route->params;

                    if (is_callable($action)) {
                        return $this->executeRouteCallback($action, $route->params);
                    }

                    $found = true;

                    break;
                }
            }
        }

        $callbackInfo = $this->handleRouteDefault($found);

        if (is_array($callbackInfo)) {
            $controller = $callbackInfo['controller'];
            $action = $callbackInfo['action'];
            $parameters = $callbackInfo['parameters'];
        }

        app(Response::class, ['data' => app()->action([$controller, $action], $parameters)])->send();
    }

    /**
     * Lấy URI nguyên mẫu.
     *
     * @return string
     */
    public function getBaseUri()
    {
        $uriGetParam = $_SERVER['REQUEST_URI'] ?? '/';
        $uriGetParam = $uriGetParam != '/' ? trim($uriGetParam, '/') : '/';
        $result = explode('?', $uriGetParam)[0];

        return $result === '' ? '/' : $result;
    }

    /**
     * Thực thi closure.
     *
     * @param  \Closure|callable  $callback
     * @param  array  $args
     * @return void
     */
    private function executeRouteCallback($callback, array $args)
    {
        $reflection = new ReflectionFunction($callback);
        $new_parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            if (! is_null($parameter->getType())) {
                $new_parameters[] = app($parameter->getType()->getName());
            } elseif (isset($args[$parameter->name])) {
                $new_parameters[] = $args[$parameter->name];
            }
        }

        $parameters = $new_parameters;

        app(Response::class, ['data' => call_user_func_array($callback, $parameters)])->send();
    }

    /**
     * Xử lý thiết lập đối route mặc định (controller/action/parameter).
     *
     * @param  bool  $status
     * @return void
     *
     * @throws \BrightMoon\Exceptions\BrightMoonRouteException
     */
    private function handleRouteDefault($status)
    {
        if (! $status) {
            if ($this->routeDefault) {
                $parts = explode('/', $_SERVER['REQUEST_URI']);
                $parts = array_filter($parts);

                return [
                    'controller' => ($c = array_shift($parts))
                        ? app(Str::of($c)->studly()->prepend("App\\Controllers\\")->append('Controller')->__toString())
                        : '',
                    'action' => ($c = array_shift($parts)) ? $c : 'index',
                    'params' => (isset($parts[0])) ? $parts : [],
                ];
            }

            throw new BrightMoonRouteException('Yêu cầu không hợp lệ. Kiểm tra lại đường dẫn hoặc phương thức.');
        }
    }

    /**
     * Nhóm các route có điểm chung lại với nhau.
     *
     * @param  array  $config
     * @param  \Closure  $callback
     * @return void
     */
    public function group(array $config, Closure $callback)
    {
        $routeGroup = new RouteGroup($this, $config);
        $routeGroup->execute($callback);
    }

    /**
     * Thiết lập các thông tin cấu hình cho route group.
     *
     * @param  array  $options
     * @return void
     */
    public function setRouteGroupOptions(array $options = [])
    {
        if (empty($options)) {
            $this->routeGroupOptions = [
                'middleware' => [],
                'namespace' => '',
                'prefix' => '',
                'as' => '',
            ];
        }

        foreach ($options as $key => $value) {
            if ($key == 'prefix') {
                $value = '/'.ltrim($value, '/');
            } elseif ($key == 'middleware') {
                if (is_string($value)) {
                    $value = [$value];
                }

                $this->routeGroupOptions[$key] = empty($this->routeGroupOptions[$key])
                    ? $value
                    : array_merge($this->routeGroupOptions[$key], $value);
                continue;
            }

            $this->routeGroupOptions[$key] .= $value;
        }
    }

    /**
     * Thiết lập cấu hình cho route chạy mặc định theo [Controller]/[action]/[id].
     *
     * @param  bool  $default
     * @return void
     */
    public function default($default = true)
    {
        $this->routeDefault = $default;
    }

    /**
     * Lấy thông tin url bằng tên của route.
     *
     * @param  string  $name
     * @return string
     */
    public function getUriByName($name)
    {
        return $this->getRouteByName($name)->getUri();
    }

    /**
     * Lấy route hiện tại bằng tên.
     *
     * @param  string|null  $name
     * @return \BrightMoon\Routing\Route
     */
    public function getRouteByName(?string $name = null)
    {
        $currentRoute = null;

        foreach ($this->routes as $method => $route) {
            foreach ($this->routes[$method] as $route) {
                if ($name && $name == $route->getName()) {
                    return $route;
                } elseif ($this->getBaseUri() == $route->getUri()) {
                    return $route;
                }

                $currentRoute = $route;
            }
        }

        return $currentRoute;
    }

    /**
     * Lấy URL hiện tại không chứa query string.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        $uri = preg_replace('/\/+/', '/', '/'.$this->getBaseUri());
        $base = trim(base_url(), '/');

        return $base.$uri;
    }

    /**
     * Lấy toàn bộ URL hiện tại
     *
     * @return string
     */
    public function fullPathCurrent()
    {
        $uri = preg_replace('/\/+/i', '/', ($_SERVER['REQUEST_URI'] ?? ''));
        $base = trim(base_url(), '/');

        return $base.$uri;
    }

    /**
     * Lấy danh sách route đã đăng ký.
     *
     * @return array
     */
    public function getListRouteRegisted()
    {
        return $this->routesRegistered;
    }

    /**
     * Xử lý gọi phương thức động của router.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \BrightMoon\Routing\RouteRegistrar
     *
     * @throws \BrightMoon\Exceptions\BrightMoonRouteException
     */
    public function __call($method, $parameters)
    {
        if (method_exists(RouteRegistrar::class, $method) || property_exists(RouteRegistrar::class, $method)) {
            return app(RouteRegistrar::class, ['router' => $this])->{$method}(...$parameters);
        }

        throw new BrightMoonRouteException('Phương thức không hợp lệ');
    }
}
