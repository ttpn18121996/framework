<?php

namespace BrightMoon\Database;

use BrightMoon\Contracts\Connection;
use \PDO;
use \PDOException;

class PostgreSQLConnection extends Connection
{
    /**
     * Khởi tạo đối tượng PostgreSQLConnection.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->connect($config);
    }

    /**
     * Kết nối cơ sở dữ liệu.
     *
     * @param  array  $config
     * @return string
     */
    public function connect(array $config)
    {
        $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";

        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        try {
            $connection = new PDO($dsn, $config['username'], $config['password'], $this->options);
        } catch (PDOException $e) {
            throw $e;
        }

        if (isset($config['charset'])) {
            $connection->prepare("SET NAMES '{$config['charset']}'")->execute();
        }

        if (isset($config['schema'])) {
            $connection->prepare("SET search_path TO {$config['schema']}")->execute();
        }

        $this->connection = $connection;
    }
}
