<?php

namespace BrightMoon\Hashing;

use BrightMoon\Support\Arr;

class Hash
{
    /**
     * Cấu hình hàm băm.
     *
     * @var array
     */
    protected $config;

    /**
     * Định nghĩa thuật toán sử dụng để băm.
     *
     * @var string
     */
    protected $driver;

    protected static $drivers = [
        'bcrypt' => BcryptHasher::class,
    ];

    public function __construct()
    {
        $this->config = config('hashing');
        $this->driver = Arr::get($this->config, 'driver');
    }

    /**
     * Lấy đối tượng băm.
     *
     * @return mixed
     */
    public function getHasher()
    {
        return app(Arr::get(static::$drivers, $this->driver), ['options' => Arr::get($this->config, $this->driver)]);
    }

    /**
     * Thêm vào các Hasher khác.
     *
     * @param  array  $hasheres
     * @return void
     */
    public static function pushHasher(array $hasheres)
    {
        static::$drivers = array_merge(static::$drivers, $hasheres);
    }

    /**
     * Xử lý gọi phương thức động của hash.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists(\BrightMoon\Contracts\Hasher::class, $method)) {
            return call_user_func_array([$this->getHasher(), $method], $parameters);
        }

        return null;
    }
}
