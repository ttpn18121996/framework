<?php

namespace BrightMoon\Database;

use BrightMoon\Database\Query\Builder as QueryBuilder;

class DatabaseManager
{
    protected $connections;

    /**
     * Factory tạo các connection.
     *
     * @var \BrightMoon\Database\ConnectionFactory
     */
    protected $factory;
    /**
     * Khởi tạo đối tượng.
     *
     * @param  \BrightMoon\Database\ConnectionFactory  $factory
     * @return void
     */
    public function __construct(ConnectionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Lấy đối tượng kết nối cơ sở dữ liệu.
     *
     * @param  string|null  $name
     * @return \BrightMoon\Contracts\Connection
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = config('database.default');
        }

        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->factory->getConnect($name);
        }

        return $this->connections[$name];
    }

    /**
     * Xử lý gọi phương thức động theo cách gọi phương thức tĩnh.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->{$method}(...$parameters);
    }
}
