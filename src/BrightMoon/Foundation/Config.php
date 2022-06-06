<?php

namespace BrightMoon\Foundation;

use BrightMoon\Support\Arr;

class Config
{
    /**
     * Biến lưu trữ đối tượng khởi tạo singleton
     *
     * @var Config
     */
    private static $instance;

    private $configs = [];

    /**
     * Khởi tạo đối tượng Config.
     *
     * @return void
     */
    private function __construct()
    {
        $configs = scandir(base_path('config'));

        foreach ($configs as $config) {
            if (! in_array($config, ['.', '..'])) {
                $this->configs[str_replace('.php', '', $config)] = require base_path("config/{$config}");
            }
        }
    }

    /**
     * Khởi tạo đối tượng singleton.
     *
     * @return \BrightMoon\Foundation\Config
     */
    public static function getInstance()
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }

        return new self;
    }

    /**
     * Lấy dữ liệu config theo tên file config.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        $array = $this->configs;

        return Arr::get($array, $key, $default);
    }
}
