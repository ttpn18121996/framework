<?php

namespace BrightMoon\Database\Query\Processors;

class PostgreSQLProcessor extends Processor
{
    /**
     * Xử lý chuỗi sql có limit.
     *
     * @param  int  $limit
     * @param  int  $offset
     * @return string
     */
    public function compileLimit($limit, $offset)
    {
        $result = '';

        if (! is_null($limit)) {
            $result .= " LIMIT {$limit}";
        }

        if (! is_null($offset)) {
            $result .= " OFFSET {$offset}";
        }

        return $result;
    }

    /**
     * Xử lý chuỗi thêm dữ liệu vào bảng.
     *
     * @param  string  $table
     * @param  array  $data
     * @return mixed
     */
    public function compileInsert($table, array $data)
    {
        $sql = "INSERT INTO \"{$table}\" (%s) VALUES %s";

        foreach ($data as $key => $value) {
            $field = '"'.implode('", "', array_keys($value)).'"';
            $values[] = '('.join(',', array_fill(0, count($value), '?')).')';
            $params[] = array_values($value);
        }

        $values = implode(',', $values);
        $sql = sprintf($sql, $field, $values);

        return compact('sql', 'params');
    }

    /**
     * Xử lý chuỗi xoá toàn bộ dữ liệu trong bảng và reset giá trị tự tăng về ban đầu.
     * Tự động xoá dữ liệu các bảng có khoá ngoại tham chiếu tới bảng.
     *
     * @param  string  $table
     * @return string
     */
    public function compileTruncate($table)
    {
        return "TRUNCATE {$table} RESTART IDENTITY CASCADE";
    }
}
