<?php

namespace BrightMoon\Foundation;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use TypeError;

class Container
{
    /**
     * Thực thể khởi tạo của container dùng cho singleton.
     *
     * @var \BrightMoon\Foundation\Container
     */
    protected static $instance;

    /**
     * Danh sách các đối tượng đăng ký cho container.
     *
     * @var array[]
     */
    protected $bindings = [];

    /**
     * Danh sách các tên thay thế cho các đối tượng dùng cho facade.
     *
     * @var array[]
     */
    protected $aliases = [];

    /**
     * Danh sách các instance đã được khởi tạo cho singleton.
     *
     * @var array[]
     */
    protected $instances = [];

    /**
     * Danh sách các danh sách tham số dùng để khởi tạo đối tượng khi resolve.
     *
     * @var array[]
     */
    protected $with = [];

    /**
     * Các kiểu dữ liệu của tham số truyền vào hàm
     *
     * @var array
     */
    protected $typeVariable = ['int', 'string', 'boolean', 'array', 'float', '', ];

    /**
     * Khai báo ràng buộc các lớp đối tượng với container.
     * Từ đó phục vụ cho việc khởi tạo đối tượng.
     *
     * @param  string  $abstract
     * @param  callable|string|null  $concrete
     * @param  bool  $shared
     * @return void
     *
     * @throws \TypeError
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (! $concrete instanceof Closure) {
            if (! is_string($concrete)) {
                throw new TypeError(self::class.'::bind(): Tham số thứ 2 ($concrete) phải là một trong các kiều Closure|string|null');
            }

            $concrete = $this->getCloure($abstract, $concrete);
        }

        if (! $shared) {
            $this->bindings[$abstract] = compact('concrete', 'shared');
        } else {
            $this->bindings[$abstract] ??= compact('concrete', 'shared');
        }
    }

    /**
     * Lấy Clousure để sử dụng khi build.
     *
     * @param  string  $abstract
     * @param  string  $concrete
     * @return \Cloure
     */
    public function getCloure($abstract, $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->resolve($concrete, $parameters);
        };
    }

    /**
     * Tương tự như bind nhưng chỉ khởi tạo duy nhất một lần.
     *
     * @param  string  $abstract
     * @param  callable|string|null  $concrete
     * @param  bool  $shared
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        return $this->bind($abstract, $concrete, true);
    }

    /**
     * Tương tự như resolve.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Giải quyết các điều kiện để khởi tạo đối tượng và trả về đối tượng đã khởi tạo.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function resolve($abstract, $parameters = [])
    {
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        $this->with[] = $parameters;

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }

        // Kiểm tra đối tượng cần khởi tạo singleton.
        if ($this->isShared($abstract)) {
            $this->instances[] = $object;
        }

        return $object;
    }

    /**
     * Xác định xem concrete có build được không.
     *
     * @param  mixed  $concrete
     * @param  string  $abstract
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Thực hiện xây dựng đối tượng và truyền dependencies 1 cách tự động (autowirirng).
     *
     * @param  string  $concrete
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        try {
            $reflector = new ReflectionClass($concrete);
            $dependencies = $this->resolveDependencies($reflector);
        } catch (ReflectionException $e) {
            throw $e;
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Phân tích lấy danh sách các dependencies (tham số các lớp phụ thuộc).
     *
     * @param  mixed  $reflector
     * @return array
     *
     * @throws \ReflectionException
     */
    public function resolveDependencies($reflector)
    {
        if (! $reflector->isInstantiable()) {
            throw new ReflectionException('Không thể thực hiện khởi tạo.');
        }

        if (! $constructor = $reflector->getConstructor()) {
            return [];
        }

        return $this->resolveParameters($constructor->getParameters(), $this->getLastParameterOverride());
    }

    /**
     * Lấy tham số cuối cùng được ghi vào $with.
     *
     * @return array
     */
    public function getLastParameterOverride()
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * Kiểm tra đối tượng đã khai báo alias chưa.
     *
     * @param  string  $name
     * @return bool
     */
    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Xác định lớp đối tượng đã được ràng buộc (đăng ký) chưa.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return (isset($this->instances[$abstract]) || isset($this->bindings[$abstract]));
    }

    /**
     * Kiểm tra đối tượng có phải singleton.
     *
     * @param   $abstract
     * @return bool
     */
    public function isShared($abstract)
    {
        return isset($this->instances[$abstract]) ||
               (isset($this->bindings[$abstract]['shared']) &&
               $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param  string  $abstract
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Thực thi phương thức của một class.
     *
     * @param  array|string|Closure  $callback
     * @param  array  $parameterOverride
     * @return mixed
     */
    public function action($callback, array $parameterOverride = [])
    {
        return $this->call($callback, $parameterOverride);
    }

    /**
     * Thực thi phương thức của một class.
     *
     * @param  array|string|Closure  $callback
     * @param  array  $parameterOverride
     * @return mixed
     */
    public function call(string|array|Closure $callback, array $parameterOverride = [])
    {
        if ($callback instanceof Closure) {
            $method = new ReflectionFunction($callback);
            $action = $callback;
        } else {
            [$class, $action] = is_string($callback) ? explode($callback, '@') : $callback;

            $instance = $this->make($class);

            $method = new ReflectionMethod($instance, $action);
            $action = [$instance, $action];
        }

        $parameters = $this->resolveParameters($method->getParameters(), $parameterOverride);

        return call_user_func_array($action, $parameters);
    }

    /**
     * Phân tích và tạo danh sách các giá trị làm đối số cho phương thức.
     *
     * @param  array  $parameters
     * @param  array  $parameterOverride
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function resolveParameters(array $parameters, array $parameterOverride = [])
    {
        $instances = [];

        foreach ($parameters as $parameter) {
            if (isset($parameterOverride[$parameter->getName()])) {
                $instances[] = $parameterOverride[$parameter->getName()];
                continue;
            }

            $parameterType = $parameter->getType();

            if (! $parameterType) {
                if (! $parameter->isDefaultValueAvailable()) {
                    throw new RuntimeException('Không xác định được kiểu dữ liệu của tham số truyền.');
                }

                $instances[] = $parameter->getDefaultValue();
            } elseif (in_array($parameterType->getName(), $this->typeVariable)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $instances[] = $parameter->getDefaultValue();
                } elseif ($parameterType->allowsNull()) {
                    $instances[] = null;
                }
            } else {
                $resolve = $this->resolve($parameterType->getName());
                $instances[] = ($resolve instanceof Closure) ? $resolve($this) : $resolve;
            }
        }

        return $instances;
    }

    /**
     * Khởi tạo singleton.
     *
     * @return \BrightMoon\Foundation\Container
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        
        return static::$instance;
    }

    /**
     * Thiết lập giá trị khởi tạo cho container.
     *
     * @param  \BrightMoon\Foundation\Container  $container
     * @return \BrightMoon\Foundation\Container|static
     */
    public static function setInstance(Container $container)
    {
        return static::$instance = $container;
    }

    /**
     * Bỏ khởi tạo singleton.
     *
     * @return void
     */
    public function forgetInstance()
    {
        static::$instance = null;
    }
}
