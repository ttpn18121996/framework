<?php

namespace BrightMoon\Database\Query;

use BrightMoon\Contracts\Support\Arrayable;
use BrightMoon\Contracts\Connection;
use BrightMoon\Database\Query\Processors\Processor;
use BrightMoon\Model;
use BrightMoon\Pagination\{AbstractPaginator, Paginator, SimplePaginator};
use BrightMoon\Support\Facades\Route;
use BrightMoon\Support\Str;
use InvalidArgumentException;
use PDO;
use PDOException;

class Builder
{
    /**
     * Đối tượng tương tác với cơ sở dữ liệu.
     *
     * @var \BrightMoon\Contracts\Connection
     */
    protected $connection;

    /**
     * Các hàm tính toán trên các cột khi truy vấn (sum, count, min, max,...).
     *
     * @var array
     */
    public $aggregate;

    /**
     * Các cột sẽ trả về sau khi truy vấn.
     *
     * @var array
     */
    public $columns = ['*'];

    /**
     * Cho biết kết quả truy vấn trả về có lấy trùng hay không.
     *
     * @var bool|array
     */
    public $distinct = false;

    /**
     * Tên bảng để truy vấn tới.
     *
     * @var string
     */
    public $from;

    /**
     * Điều kiện join các bảng trong truy vấn.
     *
     * @var array
     */
    public $joins;

    /**
     * Các điều kiện where cho truy vấn.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public $groups;

    /**
     * The having constraints for the query.
     *
     * @var array
     */
    public $havings;

    /**
     * Điều kiện sắp xếp thứ tự bảng ghi.
     *
     * @var array
     */
    public $orders;

    /**
     * Số lượng tối đa bảng ghi cần lấy.
     *
     * @var int
     */
    public $limit;

    /**
     * Số lượng bảng ghi bỏ qua.
     *
     * @var int
     */
    public $offset;

    /**
     * The query union statements.
     *
     * @var array
     */
    public $unions;

    /**
     * The maximum number of union records to return.
     *
     * @var int
     */
    public $unionLimit;

    /**
     * The number of union records to skip.
     *
     * @var int
     */
    public $unionOffset;

    /**
     * Điều kiện sắp xếp cho câu truy vấn union.
     *
     * @var array
     */
    public $unionOrders;

    /**
     * Indicates whether row locking is being used.
     *
     * @var string|bool
     */
    public $lock;

    /**
     * Danh sách tất cả các toán tử.
     *
     * @var array
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
        'in', 'not in'
    ];

    protected $processor;

    /**
     * Đối tượng trả về cho một tài nguyên sau khi truy vấn.
     *
     * @var string|null
     */
    protected $model;

    /**
     * Khởi tạo đối tượng QueryBuilder.
     *
     * @param  \BrightMoon\Contracts\Connection  $connection
     * @param  \BrightMoon\Database\Query\Processors\Processor  $processor
     * @return void
     */
    public function __construct(Connection $connection, Processor $processor)
    {
        $this->processor = $processor;
        $this->connection = $connection;
    }

    /**
     * Thiết lập đối tượng trả về cho mỗi record sau khi truy vấn.
     *
     * @param  \BrightMoon\Model|string|null  $model
     * @return $this
     */
    public function setModel($model = null)
    {
        if (is_string($model) && class_exists($model)) {
            $model = new $model;
        }

        if ($model instanceof Model) {
            $this->model = $model::class;
        }

        return $this;
    }

    /**
     * Lấy connection db.
     *
     * @return \BrightMoon\Contracts\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Thiết lập bảng và khởi tạo đối tượng QueryBulider.
     *
     * @param  string  $table
     * @return $this
     */
    public function table($table)
    {
        $this->from = $table;

        return $this;
    }

    /**
     * Thiết lập các cột cần lấy khi truy vấn.
     *
     * @param  array|list  $columns
     * @return $this
     */
    public function select($columns)
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Bổ sung các cột cần lấy khi truy vấn.
     *
     * @param  array|list  $columns
     * @return $this
     */
    public function addSelect($columns)
    {
        $this->columns = array_unique(array_merge($this->columns, (is_array($columns) ? $columns : func_get_args())));

        return $this;
    }

    /**
     * Thiết lập truy vấn lọc các giá trị trùng lặp.
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Thiết lập điều kiện where cho truy vấn.
     *
     * @param  \Closure|array|string|callable  $column
     * @param  string  $operator
     * @param  string  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = '=', $value = null, $boolean = 'AND')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        if (is_callable($column)) {
            $builder = $column(app(__CLASS__));
            $this->wheres[] = $builder->wheres;

            return $this;
        }

        [$value, $operator] = $this->prepareValueAndOperatior($value, $operator, func_num_args() === 2);

        if (is_null($value)) {
            return $this->whereNull($column, $boolean);
        }

        if ($this->invalidOperator($operator)) {
            $operator = '=';
        }

        $this->wheres[] = [$column, $operator, $value, $boolean];

        return $this;
    }

    /**
     * Thêm mảng vào câu điều kiện where.
     *
     * @param  array   $column
     * @param  string  $boolean
     * @return $this
     */
    protected function addArrayOfWheres(array $column, $boolean = 'AND', $method = 'where')
    {
        $group = count($this->wheres);

        foreach ($column as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->{$method}(...array_values($value));
            } else {
                $this->{$method}($key, '=', $value, $boolean);
            }
        }

        return $this;
    }

    /**
     * Xử lý toán tử và giá trị cho các trường hợp truyền tham số ở phương thức where.
     *
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function prepareValueAndOperatior($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif (is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['=', '<>', '!='])) {
            throw new InvalidArgumentException('Toán tử và giá trị không hợp lệ.');
        }

        return [$value, $operator];
    }

    /**
     * Xét toán tử không hợp lệ.
     *
     * @param  string  $operator
     * @return bool
     */
    public function invalidOperator($operator)
    {
        return ! in_array(Str::lower($operator), $this->operators);
    }

    /**
     * Thiết lập điều kiện where cho truy vấn với phép OR.
     *
     * @param  \Closure|array|string|callable  $column
     * @param  string  $operator
     * @param  string  $value
     * @return mixed
     */
    public function orWhere($column, $operator = '=', $value = '=')
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Thiết lập điều kiện where in cho truy vấn.
     *
     * @param  string  $column
     * @param  array   $list
     * @param  string  $boolean
     * @param  string  $not
     * @return $this
     */
    public function whereIn($column, $list = [], $boolean = 'AND', $not = 'IN')
    {
        $this->where($column, $not, $list, $boolean);

        return $this;
    }

    /**
     * Thiết lập điều kiện WHERE NOT IN cho truy vấn.
     *
     * @param  string  $column
     * @param  array   $list
     * @return \Illuminate\Model
     */
    public function whereNotIn($column, $list = [])
    {
        return $this->whereIn($column, $list, 'AND', 'NOT IN');
    }

    /**
     * Thiết lập điều kiện WHERE IS (NOT) NULL cho truy vấn.
     *
     * @param  array|string  $columns
     * @param  string  $boolean
     * @param  boolean  $not
     * @return $this
     */
    public function whereNull($columns, $boolean = 'AND', $not = false)
    {
        $operator = $not ? 'NOT NULL' : 'NULL';

        if (is_string($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $this->wheres[] = [$column, 'IS '.$operator, null, $boolean];
        }

        return $this;
    }

    /**
     * Thiết lập điều kiện WHERE IS NOT NULL cho truy vấn.
     *
     * @param  array|string  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotNull($columns, $boolean = 'AND')
    {
        return $this->whereNull($columns, $boolean, true);
    }

    /**
     * Thiết lập điều kiện WHERE IS NOT NULL cho truy vấn.
     *
     * @param  array|string  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function orWhereNull($columns)
    {
        return $this->whereNull($columns, 'OR');
    }

    /**
     * Thiết lập điều kiện WHERE IS NOT NULL cho truy vấn.
     *
     * @param  array|string  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function orWhereNotNull($columns)
    {
        return $this->whereNotNull($columns, 'OR', true);
    }

    /**
     * Thiết lập nhóm các cột truy vấn.
     *
     * @param  array|list $columns
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->groups = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Thiết lập điều kiện với các hàm tập hợp.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @param  string  $boolean
     * @return $this
     */
    public function having($column, $operator, $value, $boolean = 'AND')
    {
        $this->havings[] = [$column, $operator, $value, $boolean];

        return $this;
    }

    /**
     * Thiết lập điều kiện với các hàm tập hợp phép OR.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @return $this
     */
    public function orHaving($column, $operator, $value)
    {
        return $this->having($column, $operator, $value, 'OR');
    }

    /**
     * Thiết lặp sắp xếp dữ liệu theo trường khi truy vấn.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [$column, Str::upper($direction)];

        return $this;
    }

    /**
     * Thiết lập sắp xếp dữ liệu theo trường khi truy vấn (giảm dần).
     *
     * @param  string  $column
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * Giới hạn bản ghi.
     *
     * @param  int  $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Lấy vị trí bắt đầu khi giới hạn bản ghi.
     *
     * @param  int  $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Lấy giới hạn số lượng dòng.
     *
     * @param  int  $limit
     * @param  int  $start
     * @return $this
     */
    public function take($limit, $start = 0)
    {
        $this->limit($limit);

        $this->offset($start);

        return $this;
    }

    /**
     * Nối bảng.
     *
     * @param  string  $table
     * @param  callable|string  $first
     * @param  string  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @return $this
     */
    public function join($table, $first, $operator = '=', $second = null, $type = 'INNER')
    {
        $join = new JoinClause($this->processor, $table, $type);

        if (is_callable($first)) {
            $first($join);
            $this->joins[] = $join;

            return $this;
        }

        $this->joins[] = $join->on($first, $operator, $second);

        return $this;
    }

    /**
     * Nối bảng lấy phần của bảng bên trái.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @return $this
     */
    public function leftJoin($table, $first, $operator = '=', $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Nối bảng lấy phần của bảng bên phải.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @return $this
     */
    public function rightJoin($table, $first, $operator = '=', $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Lấy toàn bộ dữ liệu.
     *
     * @return \BrightMoon\Support\Collection
     */
    public function all()
    {
        return $this->get(['*']);
    }

    /**
     * Lấy danh sách các model.
     *
     * @param  array  $columns
     * @return \BrightMoon\Support\Collection
     */
    public function get(array $columns = [])
    {
        $result = $this->connection
            ->executeFetch($this->compileSelect($columns), $this->getParams(), 'all');

        if (! is_null($this->model)) {
            $models = [];
            foreach ($result as $key => $value) {
                $models[$key] = app($this->model, ['attributes' => $value]);
            }

            $result = $models;
        }
        
        return collect($result);
    }

    /**
     * Lấy danh sách phân trang biết tổng số trang.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageParam
     * @param  int  $page
     * @return \BrightMoon\Pagination\Paginator
     */
    public function paginate($perPage, array $columns = [], $pageParam = 'page', $page = null)
    {
        $page ??= AbstractPaginator::resolveCurrentPage($pageParam);
        $items = $this->get($columns);
        $totalPage = intval($items->count() / $perPage);
        $items = $items->slice(($page - 1) * $perPage, $perPage);

        if ($totalPage <= 1) {
            $totalPage = 1;
        } elseif ($items->count() % $perPage != 0) {
            $totalPage += 1;
        }

        return new Paginator($items, $totalPage, $perPage, $page, [
            'pageParam' => $pageParam,
            'path' => Route::getCurrentUrl()
        ]);
    }

    /**
     * Lấy danh sách phân trang.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageParam
     * @param  int  $page
     * @return mixed
     */
    public function simplePaginate($perPage, array $columns = [], $pageParam = 'page', $page = null)
    {
        $page ??= AbstractPaginator::resolveCurrentPage($pageParam);
        $items = $this->take($perPage + 1, ($page - 1) * $perPage)->get($columns);

        return new SimplePaginator($items, $perPage, $page, [
            'pageParam' => $pageParam,
            'path' => Route::getCurrentUrl()
        ]);
    }

    /**
     * Tìm tài nguyên dựa theo id.
     *
     * @param  int  $id
     * @param  array  $columns
     * @return mixed
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->where('id', $id)->first($columns);
    }

    /**
     * Lấy record đầu tiên.
     *
     * @param  array  $columns
     * @return mixed
     */
    public function first(array $columns = [])
    {
        $result = $this->connection
            ->executeFetch($this->compileSelect($columns), $this->getParams());

        if (! is_null($this->model) && ! empty($result)) {
            $result = app($this->model, ['attributes' => $result]);
        }
        
        return $result ?? null;
    }

    /**
     * Lấy danh sách tham số cho câu điều kiện truy vấn.
     *
     * @return array
     */
    public function getParams()
    {
        $params = [];
        foreach ($this->wheres as $key => $condition) {
            if (is_array($condition[0])) {
                foreach ($condition as $sub_key => $sub_condition) {
                    $params[] = $sub_condition[2];
                }
            } else {
                $params[] = $condition[2];
            }
        }

        return $params;
    }

    /**
     * Xuất câu truy vấn.
     *
     * @return string
     */
    public function toSql()
    {
        return $this->compileSelect();
    }

    /**
     * Xây dựng câu truy vấn.
     *
     * @param  array  $columns
     * @return string
     */
    public function compileSelect(array $columns = [])
    {
        if (! empty($columns)) {
            $this->columns = $columns;
        }

        if (is_null($this->from)) {
            return null;
        }

        $sql = $this->distinct
                    ? 'SELECT distinct '.implode(', ', $this->columns)
                    : 'SELECT '.implode(', ', $this->columns);
        $sql .= ' FROM '.$this->from;

        if (! empty($this->joins)) {
            $sql .= $this->processor->compileJoin($this->joins);
        }

        if (! empty($this->wheres)) {
            $sql .= $this->processor->compileWhere($this->wheres);
        }

        $sql .= $this->processor->compileLimit($this->limit, $this->offset);

        return $sql;
    }

    /**
     * Sử dụng Generator để truy vấn lấy dữ liệu khổng lồ.
     *
     * @param  array  $columns
     * @return mixed
     */
    public function cursor(array $columns = [])
    {
        $data = $this->connection
            ->executeFetch($this->compileSelect($columns), $this->getParams(), 'all');

        $result = function ($data) {
            foreach ($data as $value) {
                if (! is_null($this->model)) {
                    yield new $this->model((array) $value);
                } else {
                    yield $value;
                }
            }
        };

        return $result($data);
    }

    /**
     * Thêm một tài nguyên vào cơ sở dữ liệu.
     *
     * @param  array|Arrayable  $data
     * @return bool
     */
    public function insert(array|Arrayable $data)
    {
        if (empty($data)) {
            return true;
        }

        $data = $data instanceof Arrayable ? $data->toArray() : $data;

        if (! is_array(reset($data))) {
            $data = [$data];
        } else {
            foreach ($data as $key => $value) {
                ksort($value);

                $data[$key] = $value instanceof Arrayable ? $value->toArray() : $value;
            }
        }

        extract($this->processor->compileInsert($this->from, $data));

        return $this->connection->execute($sql, $params);
    }

    /**
     * Thêm dữ liệu và trả về id.
     *
     * @param  array  $data
     * @return int
     */
    public function insertGetId(array $data, $key = null)
    {
        if (empty($data)) {
            return null;
        }

        if (! is_array(reset($data))) {
            extract($this->processor->compileInsert($this->from, [$data]));

            return $this->connection->insertGetId($sql, $params, $key);
        }

        return null;
    }

    /**
     * Cập nhật 1 hoặc nhiều dữ liệu trong bảng.
     *
     * @param  array  $data
     * @return bool
     */
    public function update(array $data)
    {
        if (empty($data)) {
            return true;
        }

        extract($this->processor->compileUpdate($this->from, $data, $this->wheres));

        return $this->connection->execute($sql, $params);
    }

    /**
     * Xoá 1 hoặc nhiều dữ liệu trong bảng.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function delete(array|int $id)
    {
        // code...
    }

    /**
     * Xóa toàn bộ dữ liệu trong bảng và đưa bảng về trạng thái ban đầu (id bắt đầu bằng 1).
     *
     * @return bool
     * @throws \PDOException
     */
    public function truncate()
    {
        try {
            return $this->connection->execute($this->processor->compileTruncate($this->from));
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Truy vấn thuần.
     *
     * @param  string  $sql
     * @param  array   $parameters
     * @return mixed
     */
    public function raw($sql, array $parameters = [])
    {
        return $this->connection->execute($sql, $parameters);
    }
}
