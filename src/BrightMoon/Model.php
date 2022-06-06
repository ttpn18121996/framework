<?php

namespace BrightMoon;

use ArrayAccess;
use JsonSerializable;
use BrightMoon\Contracts\Support\Arrayable;
use BrightMoon\Contracts\Support\Jsonable;
use BrightMoon\Database\Relations\HasRelationships;
use BrightMoon\Support\Str;
use PDO;

abstract class Model implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    use HasRelationships;

    /**
     * Dữ liệu thu thập được của 1 bảng ghi trong cơ sở dữ liệu.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Tên bảng liên kết với model.
     *
     * @var string
     */
    protected $table;

    /**
     * Khóa chính.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Danh sách các trường không hiển thị khi chuyển thành mảng.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Danh sách các trường cho phép thêm khi thực thi lệnh create.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Loại kết nối cơ sở dữ liệu.
     *
     * @var string
     */
    protected $connection;

    /**
     * Khởi tạo đối tượng Model.
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Thêm một tài nguyên vào cơ sở dữ liệu và trả về đối tượng vừa thêm.
     *
     * @param  array  $data
     * @return $this|null
     */
    public function create(array $data)
    {
        $id = $this->insertGetId($data, $this->getKeyName());

        if ($id) {
            return $this->where($this->getKeyName(), $id)->first();
        }

        return null;
    }

    /**
     * Cập nhật nếu đã tồn tại hoặc tạo mới nếu chưa tạo.
     *
     * @return $this
     */
    public function save()
    {
        if (empty($this->attributes)) {
            return $this;
        }

        if (is_null($this->getAttribute($this->getKeyName()))) {
            return $this->create($this->attributes);
        }
    }

    /**
     * Tạo mới query builder theo cách tĩnh.
     *
     * @return \BrightMoon\Database\Query\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Tạo mới query builder.
     *
     * @return \BrightMoon\Database\Query\Builder
     */
    public function newQuery()
    {
        return app('db')
            ->connection($this->connection)
            ->setFetchMode(PDO::FETCH_ASSOC)
            ->setModel($this)
            ->table($this->getTable());
    }

    /**
     * Trả về kết quả ở dạng mảng.
     *
     * @return array
     */
    public function toArray()
    {
        if (! empty($this->hidden)) {
            return array_filter($this->attributes, function ($attribute) {
                return ! in_array($attribute, $this->hidden);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $this->attributes;
    }

    /**
     * Trả kết quả ở dạng json.
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception();
        }

        return $json;
    }

    /**
     * Chuyển đổi đối tượng thành thứ gì đó có thể tuần tự hóa JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Thêm dữ liệu vào attributes.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Lấy dữ liệu trong attributes theo key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (method_exists($this, $key)) {
            return $this->{$key}()->get();
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Lấy tên khóa chính của bảng trong cơ sở dữ liệu.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey ?? 'id';
    }

    /**
     * Đặt tên khóa chính của bảng trong cơ sở dữ liệu.
     *
     * @param  string  $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    /**
     * Lấy tên bảng liên kết với model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename(get_class($this)));
    }

    /**
     * Thiết lập tên bảng liên kết với model.
     *
     * @param  string  $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Thiết lập attributes của model theo dạng động.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Truy xuất attributes của model theo dạng động.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Gỡ bỏ một attribute của model.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Xác định một attribute và relation có tồn tại trong model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Gỡ bỏ một attribute của model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Xử lý gọi phương thức động của model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->newQuery()->{$method}(...$parameters);
    }

    /**
     * Xử lý gọi phương thức động theo cách gọi phương thức tĩnh.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Chuyển model trả về kiểu chuỗi json.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
