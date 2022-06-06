<?php

namespace BrightMoon\Database\Query;

class JoinClause extends Builder
{
    /**
     * Kiểu join.
     *
     * @var string (inner, left, right, outer,...)
     */
    public $type;

    /**
     * Tên bảng join tới.
     *
     * @var string
     */
    public $table;

    public function __construct($processor, $table, $type)
    {
        $this->processor = $processor;
        $this->table = $table;
        $this->type = $type;
    }

    /**
     * Thiết lập điều kiện join.
     *
     * @param  string  $first
     * @param  string  $operator
     * @param  string  $second
     * @param  string  $boolean
     * @return $this
     */
    public function on($first, $operator, $second, $boolean = 'AND')
    {
        $this->joins = compact('first', 'operator', 'second', 'boolean');

        return $this;
    }

    /**
     * Lấy chuỗi câu điều kiện join.
     *
     * @return string
     */
    public function getStringJoinClause()
    {
        $on= " {$this->type} JOIN {$this->table}
        ON {$this->joins['first']} {$this->joins['operator']} {$this->joins['second']}";

        if (! empty($this->wheres)) {
            $on .= ' AND '.ltrim($this->processor->compileWhere($this->wheres), ' WHERE ');
        }

        return $on;
    }
}
