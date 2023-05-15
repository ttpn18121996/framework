<?php

namespace BrightMoon\Session;

use BrightMoon\Foundation\Container;

class SessionManager
{
    protected $container;

    protected $config;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->make('config');
    }

    public function getDriverDefault()
    {
        return $this->config->get('session.driver');
    }
}
