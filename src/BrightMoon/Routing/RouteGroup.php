<?php

namespace BrightMoon\Routing;

use BrightMoon\Contracts\Support\Arrayable;
use Closure;

class RouteGroup implements Arrayable
{
    /**
     * Khởi tạo đối tượng.
     */
    public function __construct(
        protected Router $router,
        protected array $options = [
            'middlewares' => [],
            'namespace' => '',
            'prefix' => '',
            'as' => '',
        ],
    ) {
    }

    /**
     * Thực thi callback.
     */
    public function execute(Closure $callback): void
    {
        $callback();
    }

    /**
     * Thiết lập middlewares.
     */
    public function middleware(array $middlewares = []): static
    {
        $this->options['middlewares'] = array_merge($this->options['middlewares'] ?? [], $middlewares);

        return $this;
    }

    /**
     * Thiết lập prefix.
     */
    public function prefix(string $prefix = ''): static
    {
        $this->options['prefix'] = ($this->options['prefix'] ?? '').'/'.ltrim($prefix, '/');

        return $this;
    }
    
    /**
     * Thiết lập namespace.
     */
    public function namespace(string $namespace = ''): static
    {
        $this->options['namespace'] = ($this->options['namespace'] ?? '').$namespace;

        return $this;
    }
    
    /**
     * Thiết lập name.
     */
    public function name(string $name = ''): static
    {
        $this->options['as'] = ($this->options['as'] ?? '').$name;

        return $this;
    }

    public function merge(array $oldData, array $newData)
    {
        $this->options['middlewares'] = array_merge($oldData['middlewares'] ?? [], $newData['middlewares'] ?? []);
        $this->options['prefix'] = ($oldData['prefix'] ?? '').($newData['prefix'] ?? '');
        $this->options['namespace'] = ($oldData['namespace'] ?? '').($newData['namespace'] ?? '');
        $this->options['as'] = ($oldData['as'] ?? '').($newData['as'] ?? '');

        return $this;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function __get(string $key)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return null;
    }
}
