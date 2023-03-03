<?php

namespace BrightMoon\Support;

use ArrayAccess;

class Arr
{
    /**
     * Xác định xem giá trị có phải là dạng mảng hay không.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Thêm phần tử cho mảng bằng cách sử dụng dấu "chấm" nếu nó không tồn tại.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function add($array, $key, $value)
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Lấy giá trị của phần tử theo key.
     *
     * @param  array  $items
     * @param  string|array  $value
     * @param  string|array|null  $keys
     * @return array
     */
    public static function pluck($items, $value, $keys = null)
    {
        $result = [];

        foreach ($items as $item) {
            if (! is_null($keys)) {
                $key = Arr::get($item, $keys);
                if (is_string($key) || is_numeric($key)) {
                    $result[$key] = Arr::get($item, $value);
                } elseif (is_object($key) && method_exists($key, '__toString')) {
                    $result[(string) $key] = Arr::get($item, $value);
                }
            } else {
                $result[] = Arr::get($item, $value);
            }
        }

        return $result;
    }

    /**
     * Thêm một phần tử vào đầu mảng.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  mixed  $key
     * @return array
     */
    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Rút gọn mảng của các mảng thành mảng đơn.
     *
     * @param  array  $array
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (! is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Chia mảng ra làm 2 mảng. Một mảng là danh sách keys, mảng còn lại là danh sách values.
     *
     * @param  array  $array
     * @return array
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Loại trừ các phần tử được chỉ định khỏi mảng.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except(array $array, $keys)
    {
        if (is_string($keys)) {
            unset($array[$keys]);

            return $array;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Lấy phần tử đầu tiên của mảng.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function first(array $array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default instanceof Closure ? $default() : $default;
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return $default instanceof Closure ? $default() : $default;
    }

    /**
     * Lấy phần tử mảng.
     *
     * @param  mixed  $target
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($target, $key = null, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_string($key) ? explode('.', $key) : $key;

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return is_callable($target) ? $target() : $target;
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = Arr::get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (is_array($target) && isset($target[$segment])) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }

    /**
     * Chỉ lấy các phần tử được chỉ định trong mảng.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function only(array $array, $keys)
    {
        if (is_string($keys) && array_key_exists($keys, $array)) {
            return $array[$keys];
        }

        return array_filter($array, function ($value, $key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Thiết lập giá trị cho phần tử mảng lồng nhau.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}
