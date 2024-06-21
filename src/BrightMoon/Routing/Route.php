<?php

namespace BrightMoon\Routing;

use BrightMoon\Exceptions\BrightMoonRouteException;
use BrightMoon\Support\Str;
use BrightMoon\Model;
use Closure;
use ReflectionClass;
use ReflectionFunction;

class Route
{
    /**
     * Mẫu URI mà route sẽ hướng tới.
     */
    public string $uri;

    /**
     * Phương thức HTTP mà route sẽ hướng tới.
     */
    public array $methods;

    /**
     * Action mà controller sẽ thực thi.
     */
    public \Closure|array|string $action;

    /**
     * Controller xử lý yêu cầu.
     */
    public string $controller;

    /**
     * Tên route.
     */
    public string $name;

    /**
     * Namespace của controller.
     *
     * @var string
     */
    public string $namespaceController = '';

    /**
     * Các kiểu dữ liệu của tham số truyền vào hàm
     *
     * @var array
     */
    private $typeVariable = ['int', 'string', 'boolean', 'array', 'float', '', ];

    /**
     * Danh sách tham số cần truyền khi khởi tạo controller
     *
     * @var array
     */
    public $params = [];

    /**
     * Tiền tố của route.
     *
     * @var string
     */
    public $prefix = '';

    /**
     * Các param đã được bind với route.
     *
     * @var array
     */
    private $boundParameters = [];

    /**
     * Các middleware của route.
     */
    public array $middlewares = [];

    /**
     * Khởi tạo đối tượng Route.
     *
     * @param  array  $methods
     * @param  string  $uri
     * @param  \Closure|array|string  $action
     * @return void
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri != '/' ? trim($uri, '/') : '/';
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);
    }

    /**
     * Cấu hình các thông số cho route.
     */
    public function configRoute(array $options): void
    {
        $this->prefix = $options['prefix'] ?? '';

        if (! empty($options['namespace'])) {
            $this->namespace($options['namespace']);
        }

        if (isset($options['as'])) {
            $this->name = $options['as'].$this->name;
        }
    }

    /**
     * Phân tích và lấy action cho route.
     */
    private function parseAction(\Closure|array|string $action): \Closure|array|string
    {
        if (is_callable($action)) {
            return $action;
        } elseif (is_string($action)) {
            $action = ['uses' => $action];
        }

        $this->name = $action['as'] ?? '';
        
        return $this->handleAction($action['uses'] ??= $action);
    }

    /**
     * Xử lý action dạng chuỗi.
     */
    private function handleAction(\Closure|string $action): \Closure|string
    {
        if (is_callable($action)) {
            return $action;
        }

        if (is_string($action)) {
            $controllerAndAction = Str::parseCallback($action, 'index');
        } else {
            $controllerAndAction = $action;
        }

        $this->controller = $controllerAndAction[0];

        return $controllerAndAction[1] ?? 'index';
    }

    /**
     * Thiết lập tên route.
     */
    public function name(string $name): static
    {
        $this->name .= $name;

        return $this;
    }

    /**
     * Lấy tên route.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Thiết lập namespace cho controller.
     */
    public function namespace(string $namespace): static
    {
        $this->namespaceController .= '\\'.$namespace;
        $this->namespaceController .= trim($this->namespaceController, '\\');

        return $this;
    }

    /**
     * Thiếp lập các middleware cho route.
     */
    public function middleware(string|array $middlewares)
    {
        $this->middlewares = array_merge($this->middlewares, (array) $middlewares);

        return $this;
    }

    /**
     * Bind các tham số cho route, khi nhận được key tương ứng sẽ tự động resolve giá trị.
     */
    public function bind(string $key, mixed $value): static
    {
        $this->boundParameters[$key] = $value;

        return $this;
    }

    /**
     * So sánh URI với URI của route.
     */
    public function compare(string $uri): bool
    {
        $routeUri = $this->getUri();
        $pattern_regex = preg_replace("/\{([a-zA-Z_]+[a-zA-Z_\-]*?)\}/", "(?P<$1>[\w-]*)", $routeUri);
        $pattern_regex = "#^{$pattern_regex}$#";

        if (preg_match($pattern_regex, $uri, $matches)) {
            $this->setParameters($matches);

            return true;
        }

        return false;
    }

    /**
     * Thiết lập tham số.
     */
    private function setParameters(array $matches): void
    {
        foreach ($matches as $key => $value) {
            if (! is_integer($key)) {
                if (array_key_exists($key, $this->boundParameters)) {
                    $this->params[$key] = value($this->boundParameters[$key], $value);
                    continue;
                }

                $this->params[$key] = $value;
            }
        }

        $this->bindModelToParameter();
    }

    private function bindModelToParameter()
    {
        if ($this->action instanceof Closure) {
            $method = new ReflectionFunction($this->action);
        } else {
            $class = new ReflectionClass($this->getController());
            $method = $class->getMethod($this->action);
        }

        if (! is_null($method)) {
            $parameters = $method->getParameters();
        }

        foreach ($parameters as $parameter) {
            $parameterType = $parameter->getType()?->getName();

            if (is_subclass_of($parameterType, Model::class) &&
                array_key_exists($parameter->getName(), $this->params) &&
                ! array_key_exists($parameter->getName(), $this->boundParameters)
            ) {
                $this->params[$parameter->getName()] = app($parameterType)->find($this->params[$parameter->getName()]);
            }
        }
    }

    /**
     * Thiết lập controller cho route.
     *
     * @throws \BrightMoon\Exceptions\BrightMoonRouteException
     */
    public function getController(): ?string
    {
        if (! is_null($this->controller)) {
            $controllerName = $this->namespaceController.'\\'.$this->controller;
            
            if(! class_exists($controllerName)) {
                throw new BrightMoonRouteException("Không tìm thấy controller [{$controllerName}]");
            }

            return $controllerName;
        }
        
        return 'Closure';
    }

    /**
     * Lấy URI nguyên mẫu.
     */
    private function getBaseUri(): string
    {
        return ltrim($this->uri, '/');
    }

    /**
     * Lấy URI của route.
     */
    public function getUri(): string
    {
        $uri = trim($this->prefix, '/').'/'.$this->getBaseUri();

        return $uri == '/' ? '/' : trim($uri, '/') ;
    }
}
