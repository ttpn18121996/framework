<?php

namespace BrightMoon\Hashing;

class Hash
{
    /**
     * Cấu hình hàm băm.
     *
     * @var array
     */
    private $config;

    /**
     * Định nghĩa thuật toán sử dụng để băm.
     *
     * @var string
     */
    private $driver;

    public function __construct()
    {
        $this->config = config('hashing');
        $this->driver = $this->config['driver'];
    }

    /**
     * Lấy đối tượng băm.
     *
     * @return mixed
     */
    public function getHasher()
    {
        switch ($this->driver) {
            case 'bcrypt':
                return new BcryptHasher($this->config['bcrypt']);
            
            default:
                return new BcryptHasher($this->config['bcrypt']);
        }
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
