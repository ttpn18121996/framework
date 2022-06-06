<?php

namespace BrightMoon\Support;

use JsonSerializable;

class Stringable implements JsonSerializable
{
    protected $value;

    /**
     * Khởi tạo lớp đối tượng Stringable.
     *
     * @param  string  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    /**
     * Chèn các chuỗi vào sau giá trị hiện tại.
     *
     * @param  array  $values
     * @return static
     */
    public function append(...$values)
    {
        return new static($this->value.implode('', $values));
    }

    /**
     * Lấy thành phần tên theo sau của đường dẫn.
     *
     * @param  string  $suffix
     * @return static
     */
    public function basename($suffix = '')
    {
        return new static(basename($this->value, $suffix));
    }

    /**
     * Dựa vào ký tự chỉ định phân tách chuỗi thành 1 mảng các chuỗi.
     *
     * @param  string  $separator
     * @return \BrightMoon\Support\Collection
     */
    public function explode($separator = ' ')
    {
        return new Collection(explode($separator, $this));
    }

    /**
     * Gọi hàm xử lý và trả về một chuỗi mới.
     *
     * @param callable $callback
     * @return static
     */
    public function pipe(callable $callback)
    {
        return new static(call_user_func($callback, $this));
    }

    /**
     * Chèn các chuỗi vào trước giá trị hiện tại.
     *
     * @param  array  $values
     * @return static
     */
    public function prepend(...$values)
    {
        return new static(implode('', $values).$this->value);
    }

    /**
     * Cắt các ký tự đã chỉ định khỏi chuỗi.
     *
     * @param  string  $characters
     * @return static
     */
    public function trim($characters = null)
    {
        return new static(trim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Cắt các ký tự đã chỉ định khỏi chuỗi ở phía bên trái.
     *
     * @param  string  $characters
     * @return static
     */
    public function ltrim($characters = null)
    {
        return new static(ltrim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Cắt các ký tự đã chỉ định khỏi chuỗi ở phía bên phải.
     *
     * @param  string  $characters
     * @return static
     */
    public function rtrim($characters = null)
    {
        return new static(rtrim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Đổ giá trị của chuỗi ra.
     *
     * @return $this
     */
    public function dump()
    {
        (new Dumper)->dump($this->value);

        return $this;
    }

    /**
     * Đổ giá trị của chuỗi ra và kết thúc.
     *
     * @return void
     */
    public function dd()
    {
        $this->dump();

        exit(1);
    }

    /**
     * Tên gọi khác của hàm __toString.
     *
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Chuỗi toàn bộ đối tượng này thành chuỗi khi chuyển JSON.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * Xử lý khi gọi một phương thức động của Str.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $str = forward_static_call_array([Str::class, $method], array_merge([$this->value], $parameters));

        return new static($str);
    }

    /**
     * Thuộc tính động của proxy lên các phương thức.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->{$key}();
    }

    /**
     * Xử lý trả về giá trị chuỗi khi in.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
