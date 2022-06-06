<?php

namespace BrightMoon\Database\Query\Processors;

use BrightMoon\Contracts\Connection;

class Processor
{
    /**
     * Danh sách các giá trị làm tham số cho câu truy vấn.
     *
     * @var array
     */
    protected $paramsCondition = [];

    public function getProcessor($name = null)
    {
        if (is_null($name)) {
            $config = config('database.connections.'.config('database.default'));
        } else {
            $config = config("database.connections.{$name}");
        }

        switch ($config['driver']) {
            case Connection::MYSQL:
                return new MySQLProcessor();
            case Connection::POSTGRESQL:
                return new PostgreSQLProcessor();
            default:
                return null;
        }
    }

    /**
     * Lấy danh sách tham số cho câu điều kiện truy vấn.
     *
     * @return array
     */
    public function getParamsCondition()
    {
        return $this->paramsCondition;
    }

    /**
     * Xử lý chuỗi sql có điều kiện where.
     *
     * @param  array  $wheres
     * @return string
     */
    public function compileWhere(array $wheres)
    {
        $sql = ' WHERE ';
        $params = [];

        foreach ($wheres as $key => $condition) {
            if ($key != 0) {
                if (is_array($condition[0])) {
                    $sql .= " {$condition[0][3]} ";
                } else {
                    $sql .= " {$condition[3]} ";
                }
            }

            if (is_array($condition[0])) {
                foreach ($condition as $sub_key => $sub_condition) {
                    if ($sub_key == 0) {
                        $sql .= '(';
                    } else {
                        $sql .= " {$sub_condition[3]} ";
                    }

                    $sql .= "{$sub_condition[0]} {$sub_condition[1]}".(is_null($sub_condition[2]) ? '' : ' ?');
                    $params[] = $sub_condition[2];
                }

                $sql .= ')';
            } else {
                if ($condition[1] == 'IN' || $condition[1] == 'NOT IN') {
                    $sql .= "{$condition[0]} {$condition[1]} ("
                    .join(',', array_fill(0, count($condition[2]), '?')).")";
                } else {
                    $sql .= "{$condition[0]} {$condition[1]}".(is_null($condition[2]) ? '' : ' ?');
                }

                $params[] = $condition[2];
            }
        }

        $this->paramsCondition = $params;

        return $sql;
    }

    /**
     * Xử lý chuỗi sql có điều kiện join.
     *
     * @param  array  $joins
     * @return string
     */
    public function compileJoin(array $joins)
    {
        $result = '';

        foreach ($joins as $join) {
            $result .= $join->getStringJoinClause();
        }

        return $result;
    }
}
