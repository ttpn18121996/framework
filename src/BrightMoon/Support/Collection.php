<?php

namespace BrightMoon\Support;

use ArrayAccess;
use ArrayIterator;
use BrightMoon\Contracts\Support\Arrayable;
use BrightMoon\Contracts\Support\Jsonable;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Collection implements ArrayAccess, Arrayable, Jsonable, IteratorAggregate, Countable
{
    /**
     * Danh sách các giá trị lưu trữ.
     *
     * @var array
     */
    protected $items = [];

    protected $position = 0;

    /**
     * Khởi tạo đối tượng Collection.
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
        $this->position = 0;
    }

    /**
     * Lấy giá trị kiểu mảng.
     *
     * @param  mixed  $items
     * @return array
     */
    public function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Thêm item cho Collection.
     *
     * @param  mixed  $item
     * @return $this
     */
    public function add($item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Lấy tất cả items của Collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Rút gọn mảng của các mảng thành mảng đơn.
     *
     * @return static
     */
    public function collapse()
    {
        return new static(Arr::collapse($this->items));
    }

    /**
     * Hợp hai mảng thành một với các phần tử mảng 1 là key và các phần tử mảng 2 là value.
     *
     * @param  array  $values
     * @return $this
     */
    public function combine($values)
    {
        return new static(array_combine($this->all(), $this->getArrayableItems($values)));
    }

    /**
     * Đếm số lượng items.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Duyệt qua toàn bộ phần tử mảng.
     *
     * @param  callable  $callback
     * @return void
     */
    public function each($callback)
    {
        if ($this->count()) {
            foreach ($this->items as $key => $item) {
                $callback($item, $key);
            }
        }
    }

    /**
     * Lấy tất cả các phần tử và loại trừ các phần tử được chỉ định.
     *
     * @param  string|array  $keys
     * @return static
     */
    public function except($keys)
    {
        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Lọc mảng theo điều kiện cho từng phần tử.
     *
     * @param  callable|null  $callback
     * @return array
     */
    public function filter(callable $callback)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Lấy phần tử đầu tiên của collection.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Chuyển mảng thành chuỗi.
     *
     * @param  string  $separator
     * @param  string  $separatorEnd
     * @return string
     */
    public function join($separator, $separatorEnd = '')
    {
        if ($separatorEnd === '') {
            return implode($separator, $this->items);
        }

        $count = $this->count();

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return end($this->items);
        }

        $collection = new static($this->items);

        $finalItem = $collection->pop();

        return $collection->join($separator).$separatorEnd.$finalItem;
    }

    /**
     * Thực hiện map dữ liệu trên từng phần tử của items.
     *
     * @param  callable  $callback
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Hợp nhất 2 mảng lại với nhau.
     *
     * @param  mixed  $items
     * @return static
     */
    public function merge($items)
    {
        $items = $this->getArrayableItems($items);

        return new static(array_merge($this->items, $items));
    }

    /**
     * Chỉ lấy các phần tử được chỉ định.
     *
     * @param  string|array  $keys
     * @return static
     */
    public function only($keys)
    {
        return new static(Arr::only($this->items, $keys));
    }

    /**
     * Lấy giá trị của phần tử theo key.
     *
     * @param   $key
     * @return mixed
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Lấy và xóa phần tử cuối cùng trong collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Thêm một phần tử vào đầu mảng collection.
     *
     * @param  mixed  $value
     * @param  mixed  $key
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        $this->items = Arr::prepend($this->items, $value, $key);

        return $this;
    }

    /**
     * Cắt mảng theo từ một vị trí và số lượng phần tử.
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @return static
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Đổ giá trị của chuỗi ra.
     *
     * @return $this
     */
    public function dump()
    {
        (new Dumper)->dump($this->items);

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
     * Chuyển đối tượng thành JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->all());
    }

    /**
     * Lấy danh sách các giá trị của Collection dạng mảng.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        })->all();
    }

    /**
     * Lấy danh sách các giá trị của Collection dạng JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function &offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }
}
