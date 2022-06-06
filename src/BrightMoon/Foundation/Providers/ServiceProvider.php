<?php

namespace BrightMoon\Foundation\Providers;

abstract class ServiceProvider
{
    /**
     * @var \BrightMoon\Foundation\Application
     */
    protected $app;

    public function __construct(\BrightMoon\Foundation\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Đăng ký các tính năng cho ServiceProvider.
     *
     * @return void
     */
    abstract public function register();
}
