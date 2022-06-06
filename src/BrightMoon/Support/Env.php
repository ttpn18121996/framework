<?php

namespace BrightMoon\Support;

use Dotenv\Dotenv;

class Env
{
    private $config = [];
    private static $instance;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(app()->basePath());
        $dotenv->load();
        $this->config = $_ENV;
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function get($key, $default = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Lấy toàn bộ thông tin cấu hình môi trường.
     *
     * @param  
     * @return mixed
     */
    public function all()
    {
        return $this->config;
    }

    public function __set($name, $value)
    {
        if (!isset($this->config[$name])) {
            $this->config[$name] = $value;
        }

    }

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        return null;
    }
}
