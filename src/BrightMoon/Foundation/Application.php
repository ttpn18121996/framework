<?php

namespace BrightMoon\Foundation;

use BrightMoon\Support\Arr;
use BrightMoon\Support\Facades\Route;
use BrightMoon\Foundation\Providers\AppServiceProvider;
use BrightMoon\Foundation\Providers\RouteServiceProvider;

class Application extends Container
{
    const VERSION = '1.0.1';

    protected $basePath;

    /**
     * @var \BrightMoon\Foundation\Providers\ServiceProvider[]
     */
    protected $serviceProviders = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Khởi tạo đối tượng Application.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Khai báo khởi chạy các thiết lập mặc định cho provider.
     *
     * @return void
     */
    public function registerBaseBindings()
    {
        static::setInstance($this);

        $appConfig = Config::getInstance()->getConfig('app');
        $this->instances = $appConfig['instances'] ?? [];
        $this->serviceProviders = $appConfig['providers'] ?? [];
        $this->aliases = [
            'hash' => \BrightMoon\Hashing\Hash::class,
            'db' => \BrightMoon\Database\DatabaseManager::class,
            'request' => \BrightMoon\Http\Request::class,
            'route' => \BrightMoon\Routing\Router::class,
            'view' => \BrightMoon\View::class,
        ];
    }

    /**
     * Khai báo khởi chạy các ServiceProvider mặc định.
     *
     * @return mixed
     */
    public function registerBaseServiceProviders()
    {
        $this->register(new AppServiceProvider($this));
        $this->register(new RouteServiceProvider($this));
    }

    /**
     * Đăng ký provider.
     *
     * @param  \BrightMoon\Foundation\Providers\ServiceProvider|string  $provider
     * @return \BrightMoon\Foundation\Providers\ServiceProvider
     */
    public function register($provider)
    {
        if ($registed = $this->getProvider($provider)) {
            return $registed;
        }

        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        return $provider;
    }

    /**
     * Xử lý khởi tạo provider.
     *
     * @param  string $provider
     * @return \BrightMoon\Foundation\Providers\ServiceProvider
     */
    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    /**
     * Lấy provider đã đăng ký.
     *
     * @param  \BrightMoon\Foundation\Providers\ServiceProvider|string  $provider
     * @return \BrightMoon\Foundation\Providers\ServiceProvider|null
     */
    public function getProvider($provider)
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    /**
     * Lấy danh sách các provider liên quan.
     *
     * @param  \BrightMoon\Foundation\Providers\ServiceProvider|string  $provider
     * @return array
     */
    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return array_filter($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Đăng ký các tên định danh (alias) cho các class.
     *
     * @return mixed
     */
    public function registerCoreContainerAliases()
    {
        foreach (Config::getInstance()->getConfig('app')['aliases'] as $alias => $class) {
            class_alias($class, $alias);
        }
    }

    /**
     * Chạy lần đầu.
     *
     * @return void
     */
    public function init()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            return (new \BrightMoon\Exceptions\Handler(new \BrightMoon\Exceptions\BrightMoonException($errstr, $errno)))
                    ->render($errfile, $errline);
        });
        set_exception_handler(function ($handler) {
            return (new \BrightMoon\Exceptions\Handler($handler))->render($handler->getFile(), $handler->getLine());
        });

        date_default_timezone_set(config('app.timezone'));

        foreach ($this->serviceProviders as $key => $serviceProvider) {
            if (is_string($serviceProvider)) {
                $serviceProvider = $this->register($serviceProvider);
            }

            if (method_exists($serviceProvider, 'boot')) {
                $serviceProvider->boot();
            }
        }
    }

    /**
     * Lấy đường dẫn gốc.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        foreach (Arr::get($composer, 'autoload.psr-4', []) as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->basePath('app')) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Không thể lấy được namespace của app.');
    }
}
