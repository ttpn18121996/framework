<?php

namespace BrightMoon\Support\Facades;

abstract class Facade
{
    /**
     * Biến lưu các đối tượng khởi tạo.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * Lấy đối tượng gốc.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Khởi tạo đối tượng gốc.
     *
     * @param  string|object  $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (! isset(static::$resolvedInstance[$name])) {
            static::$resolvedInstance[$name] = app($name);
        }

        return static::$resolvedInstance[$name];
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('Facade chưa được thiết lập.');
        }

        return $instance->$method(...$args);
    }
}