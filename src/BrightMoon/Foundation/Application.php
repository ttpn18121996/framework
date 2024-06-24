<?php

namespace BrightMoon\Foundation;

use BrightMoon\Support\Arr;
use BrightMoon\Foundation\Providers\AppServiceProvider;
use BrightMoon\Foundation\Providers\RouteServiceProvider;
use BrightMoon\Foundation\Providers\ServiceProvider;
use BrightMoon\Http\Request;
use BrightMoon\Support\Facades\Route;

class Application extends Container
{
    public const VERSION = '1.1.1';

    protected ?string $basePath;

    /**
     * @var \BrightMoon\Foundation\Providers\ServiceProvider[]
     */
    protected array $serviceProviders = [];

    protected ?string $namespace = null;

    protected array $config = [];

    /**
     * Khởi tạo đối tượng Application.
     */
    public function __construct(?string $basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Lấy phiên bản hiện tại của framework.
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * Khai báo khởi chạy các thiết lập mặc định cho provider.
     */
    public function registerBaseBindings(): void
    {
        static::setInstance($this);

        $this->config = Config::getInstance()->getConfig('app');
        $this->instances = $this->config['instances'] ?? [];
        $this->aliases = [
            'hash' => \BrightMoon\Hashing\Hash::class,
            'db' => \BrightMoon\Database\DatabaseManager::class,
            'request' => \BrightMoon\Http\Request::class,
            'route' => \BrightMoon\Routing\Router::class,
            'session' => \BrightMoon\Session\SessionManager::class,
            'view' => \BrightMoon\View::class,
        ];
    }

    /**
     * Khai báo khởi chạy các ServiceProvider mặc định.
     */
    public function registerBaseServiceProviders(): void
    {
        $this->serviceProviders[] = $this->register(new AppServiceProvider($this));
        $this->serviceProviders[] = $this->register(new RouteServiceProvider($this));

        if (file_exists($this->basePath('bootstrap/providers.php'))) {
            $providers = require $this->basePath('bootstrap/providers.php');
        } else {
            $providers = $this->config['providers'] ?? [];
        }

        $this->serviceProviders = array_merge($this->serviceProviders, array_map(function (ServiceProvider|string $serviceProvider) {
            if (is_string($serviceProvider)) {
                return $this->register($serviceProvider);
            }

            $serviceProvider->register();

            return $serviceProvider;
        }, $providers ?? []));
    }

    /**
     * Đăng ký provider.
     */
    public function register(ServiceProvider|string $provider): ServiceProvider
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
     */
    public function resolveProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Lấy provider đã đăng ký.
     */
    public function getProvider(ServiceProvider|string $provider): ?ServiceProvider
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    /**
     * Lấy danh sách các provider liên quan.
     */
    public function getProviders(ServiceProvider|string $provider): array
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return array_filter($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Đăng ký các tên định danh (alias) cho các class.
     */
    public function registerCoreContainerAliases(): void
    {
        foreach (Config::getInstance()->getConfig('app')['aliases'] as $alias => $class) {
            class_alias($class, $alias);
        }
    }

    /**
     * Chạy lần đầu.
     */
    public function run(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            return (new \BrightMoon\Exceptions\Handler(new \BrightMoon\Exceptions\BrightMoonException($errstr, $errno)))
                    ->render($errfile, $errline);
        });
        set_exception_handler(function ($handler) {
            return (new \BrightMoon\Exceptions\Handler($handler))->render($handler->getFile(), $handler->getLine());
        });

        date_default_timezone_set(config('app.timezone'));

        foreach ($this->serviceProviders as $serviceProvider) {
            if (method_exists($serviceProvider, 'boot')) {
                $serviceProvider->boot();
            }
        }

        Route::run($this->make(Request::class));
    }

    /**
     * Lấy đường dẫn gốc.
     */
    public function basePath(?string $path = null): string
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * Lấy namespace của app.
     */
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

    /**
     * Chạy lần đầu.
     * @deprecated Gọi hàm run() để thay thế, init() sẽ bị gỡ bỏ
     */
    public function init(): void
    {
        $this->run();
    }
}
