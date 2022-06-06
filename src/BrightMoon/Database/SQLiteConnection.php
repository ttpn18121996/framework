<?php

namespace BrightMoon\Database;

use BrightMoon\Contracts\Connection;

class SQLiteConnection extends Connection
{
    /**
     * Khởi tạo đối tượng SQLiteConnection.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->connectDatabase($config);
    }

    /**
     * Thực thi câu truy vấn.
     *
     * @param  string  $sql
     * @param  array   $data
     * @return \PDOStatement
     */
    public function execute($sql, array $data = [])
    {
        # code...
    }
}
