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
     */
    public array $routes = [];

    /**
     * Các phương thức truyền cho router.
     */
    public array $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', ];

    /**
     * Thiết lập route mặc định không cần định nghĩa (controller/action/parameter).
     */
    public bool $routeDefault = false;

    /**
     * Danh sách các routes đăng ký trong provider.
     */
    public array $routesRegistered = [];

    /**
     * Danh sách các group đã đăng ký.
     */
    protected array $groupStack = [];

    /**
     * Thiết lập route với phương thức GET.
     */
    public function get(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức POST.
     */
    public function post(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức PUT.
     */
    public function put(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức PATCH.
     */
    public function patch(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức DELETE.
     */
    public function delete(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Thiết lập route với phương thức OPTIONS.
     */
    public function options(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute(['OPTIONS'], $uri, $action);
    }

    /**
     * Thiết lập route với mọi phương thức.
     */
    public function any(string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute($this->verbs, $uri, $action);
    }

    /**
     * Thiết lập route với phương thức được định nghĩa cụ thể.
     */
    public function match(array|string $methods, string $uri, \Closure|array|string|callable $action): Route
    {
        return $this->addRoute((array) $methods, $uri, $action);
    }

    /**
     * Thêm route vào danhs sách.
     */
    public function addRoute(array|string $methods, string $uri, \Closure|array|string|callable $action): Route
    {
        $route = new Route($methods, $uri, $action);
        $routeGroupOptions = end($this->groupStack);
        $route->configRoute($routeGroupOptions);
        
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
            [$controller, $action, $parameters] = $callbackInfo;
        }

        // Execute middleware.

        app(Response::class, ['data' => app()->action([$controller, $action], $parameters)])->send();
    }

    /**
     * Lấy URI nguyên mẫu.
     */
    public function getBaseUri(): string
    {
        $uriGetParam = $_SERVER['REQUEST_URI'] ?? '/';
        $uriGetParam = $uriGetParam != '/' ? trim($uriGetParam, '/') : '/';
        $result = explode('?', $uriGetParam)[0];

        return $result === '' ? '/' : $result;
    }

    /**
     * Thực thi closure.
     */
    private function executeRouteCallback(\Closure|callable $callback, array $args): void
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
     * @throws \BrightMoon\Exceptions\BrightMoonRouteException
     */
    private function handleRouteDefault(bool $status): bool|array
    {
        if ($status) {
            return true;
        }

        if (! $this->routeDefault) {
            throw new BrightMoonRouteException('Yêu cầu không hợp lệ. Kiểm tra lại đường dẫn hoặc phương thức.');
        }

        $parts = explode('/', $_SERVER['REQUEST_URI']);
        $parts = array_filter($parts);

        return [
            'controller' => ($controller = array_shift($parts))
                ? app(Str::of($controller)->studly()->prepend("App\\Controllers\\")->append('Controller')->toString())
                : '',
            'action' => ($action = array_shift($parts)) ? $action : 'index',
            'params' => (isset($parts[0])) ? $parts : [],
        ];
    }

    /**
     * Nhóm các route có điểm chung lại với nhau.
     */
    public function group(array $options, Closure $callback): void
    {
        $this->updateGroupStack($options);

        $callback($this);
        
        array_pop($this->groupStack);
    }

    public function updateGroupStack(array $options): void
    {
        $routeGroup = new RouteGroup($this, $options);

        if (empty($this->groupStack)) {
            $this->groupStack[] = $routeGroup;
        } else {
            $lastGroupStack = end($this->groupStack);
            $this->groupStack[] = $routeGroup->merge($lastGroupStack->toArray(), $options);
        }
    }

    /**
     * Thiết lập cấu hình cho route chạy mặc định theo [Controller]/[action]/[id].
     */
    public function default(bool $default = true): void
    {
        $this->routeDefault = $default;
    }

    /**
     * Lấy thông tin url bằng tên của route.
     */
    public function getUriByName(string $name): string
    {
        return $this->getRouteByName($name)->getUri();
    }

    /**
     * Lấy route hiện tại bằng tên.
     */
    public function getRouteByName(?string $name = null): ?Route
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
     */
    public function getCurrentUrl(): string
    {
        $uri = preg_replace('/\/+/', '/', '/'.$this->getBaseUri());
        $base = trim(base_url(), '/');

        return $base.$uri;
    }

    /**
     * Lấy toàn bộ URL hiện tại.
     */
    public function fullPathCurrent(): string
    {
        $uri = preg_replace('/\/+/i', '/', ($_SERVER['REQUEST_URI'] ?? ''));
        $base = trim(base_url(), '/');

        return $base.$uri;
    }

    /**
     * Lấy danh sách route đã đăng ký.
     */
    public function getListRouteRegisted(): array
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
