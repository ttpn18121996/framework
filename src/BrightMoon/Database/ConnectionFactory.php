<?php

namespace BrightMoon\Database;

use BrightMoon\Contracts\Connection;
use BrightMoon\Support\Env;

class ConnectionFactory
{
    /**
     * Khởi tạo đối tượng Database.
     *
     * @param  string|null  $name
     * @return \BrightMoon\Contracts\Connection
     */
    public function getConnect($name = null)
    {
        if (is_null($name)) {
            $config = config('database.connections.'.config('database.default'));
        } else {
            $config = config("database.connections.{$name}");
        }

        switch ($config['driver']) {
            case Connection::MYSQL:
                return new MySQLConnection($config);
            case Connection::POSTGRESQL:
                return new PostgreSQLConnection($config);
            case Connection::SQLSERVER:
                return new SQLServerConnection($config);
            case Connection::SQLITE:
                return new SQLiteConnection($config);
            default:
                return null;
        }
    }
}
