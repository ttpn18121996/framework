<?php

namespace BrightMoon\Contracts;

use BrightMoon\Database\Query\Processors\Processor;
use BrightMoon\Database\Query\Builder as QueryBuilder;
use PDO;

abstract class Connection
{
    const MYSQL = 'mysql';
    const SQLSERVER = 'sqlsrv';
    const POSTGRESQL = 'pgsql';
    const SQLITE = 'sqlite';

    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * Đối tượng kết nối database.
     *
     * @var \PDO
     */
    protected $connection;

    /**
     * Kiểu dữ liệu trả về khi fetch data từ database.
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * Kết nối cơ sở dữ liệu.
     *
     * @param  array  $config
     * @return string
     */
    abstract public function connect(array $config);

    /**
     * Thiết lập kiểu dữ liệu trả về khi fetch data từ database.
     *
     * @param  int  $mode
     * @param  string|null  $className
     * @return $this
     */
    public function setFetchMode($mode)
    {
        $this->fetchMode = $mode;

        return $this;
    }

    /**
     * Thực thi câu truy vấn.
     *
     * @param  string  $sql
     * @param  array   $parameters
     * @return \PDOStatement
     */
    public function execute($sql, array $parameters = [])
    {
        $stm = $this->connection->prepare($sql);
        $params = [];

        if (! empty($parameters)) {
            foreach ($parameters as $parameter) {
                if (is_null($parameter)) {
                    continue;
                }

                if (is_array($parameter)) {
                    $params = array_merge($params, $parameter);
                } else {
                    $params[] = $parameter;
                }
            }
        }

        $stm->setFetchMode($this->fetchMode);
        $stm->execute($params);

        return $stm;
    }

    public function executeFetch($sql, array $parameters = [], $type = 'fetch')
    {
        $pdo = $this->execute($sql, $parameters);

        if ($type == 'fetch') {
            return $pdo->fetch();
        }

        return $pdo->fetchAll();
    }

    /**
     * Thêm dữ liệu vào cơ sở dữ liệu và trả về id.
     *
     * @param  string  $sql
     * @param  array   $data
     * @return mixed
     */
    public function insertGetId($sql, array $data = [], $key = null)
    {
        $this->execute($sql, $data);

        return $this->connection->lastInsertId($key);
    }

    /**
     * Xử lý gọi phương thức động của database manager.
     *
     * @param  string  $method
     * @param  array  $params
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return app(QueryBuilder::class, [
            'connection' => $this,
            'processor' => app(\BrightMoon\Database\Query\Processors\Processor::class)->getProcessor(),
        ])->{$method}(...$parameters);
    }
}
