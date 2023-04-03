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
     *
     * @var string
     */
    public $uri;

    /**
     * Phương thức HTTP mà route sẽ hướng tới.
     *
     * @var array
     */
    public $methods;

    /**
     * Action mà controller sẽ thực thi.
     *
     * @var string|callable
     */
    public $action;

    /**
     * Controller xử lý yêu cầu.
     *
     * @var string
     */
    public $controller;

    /**
     * Tên route.
     *
     * @var string
     */
    public $name;

    /**
     * Namespace của controller.
     *
     * @var string
     */
    public $namespaceController;

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
     * Khởi tạo đối tượng Route.
     *
     * @param  array  $methods
     * @param  string  $uri
     * @param  \Closure|array|string  $action
     * @return mixed
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri != '/' ? trim($uri, '/') : '/';
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);
    }

    /**
     * Cấu hình các thông số cho route.
     *
     * @param  array  $options
     * @return void
     */
    public function configRoute(array $options)
    {
        $this->prefix = $options['prefix'] ?? '';

        if (! empty($options['namespace'])) {
            if (empty($this->namespaceController)) {
                $this->namespaceController = $options['namespace'];
            } else {
                $this->namespaceController .= '\\'.$options['namespace'];
            }
        }

        if (isset($options['as'])) {
            $this->name = $options['as'].$this->name;
        }
    }

    /**
     * Phân tích và lấy action cho route.
     *
     * @param  \Closure|array|string  $action
     * @return \Closure|string
     */
    private function parseAction($action)
    {
        if (is_callable($action)) {
            return $action;
        } elseif (is_string($action)) {
            $action = ['uses' => $action];
        }

        $this->name = $action['as'] ?? null;
        
        return $this->handleAction($action['uses'] ??= $action);
    }

    /**
     * Xử lý action dạng chuỗi
     *
     * @param  \Closure|array|string  $action
     * @return mixed
     */
    private function handleAction($action)
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
     *
     * @param  string  $name
     * @return void
     */
    public function name($name)
    {
        $this->name .= $name;

        return $this;
    }

    /**
     * Lấy tên route.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Thiết lập namespace cho controller.
     *
     * @param  string  $namespace
     * @return $this
     */
    public function namespace($namespace)
    {
        $this->namespaceController .= '\\'.$namespace;
        $this->namespaceController .= trim($this->namespaceController, '\\');

        return $this;
    }

    /**
     * Bind các tham số cho route, khi nhận được key tương ứng sẽ tự động resolve giá trị.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function bind($key, $value)
    {
        $this->boundParameters[$key] = $value;

        return $this;
    }

    /**
     * So sánh URI với URI của route
     *
     * @param  string  $uri
     * @return bool
     */
    public function compare($uri)
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
     *
     * @param  array  $matches
     * @return void
     */
    private function setParameters(array $matches)
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
     * @param  string  $controllerName
     * @return string|null
     *
     * @throws \BrightMoon\Exceptions\BrightMoonRouteException
     */
    public function getController()
    {
        if (! is_null($this->controller)) {
            $controllerName = $this->namespaceController.'\\'.$this->controller;
            if(! class_exists($controllerName)) {
                throw new BrightMoonRouteException("Không tìm thấy controller [{$controllerName}]");
            }

            return $controllerName;
        }
        
        return null;
    }

    /**
     * Lấy URI nguyên mẫu.
     *
     * @return string
     */
    private function getBaseUri()
    {
        return ltrim($this->uri, '/');
    }

    /**
     * Lấy URI của route.
     *
     * @return string
     */
    public function getUri()
    {
        $uri = trim($this->prefix, '/').'/'.$this->getBaseUri();

        return $uri == '/' ? '/' : trim($uri, '/') ;
    }
}
