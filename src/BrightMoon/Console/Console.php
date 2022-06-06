<?php

namespace BrightMoon\Console;

abstract class Console
{

    /**
     * Xử lý lệnh.
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Lấy namespace.
     *
     * @param  string  $arg
     * @return mixed
     */
    protected function getNamespace($value)
    {
        $last = str_replace('/', '\\', $value);
        $last = str_replace($this->getClassName($value), '', $last);
        $namespace = $this->namespace.'\\'.$last;

        return rtrim($namespace, '\\');
    }

    /**
     * Lấy tên lớp đối tượng.
     *
     * @param  string  $value
     * @return string
     */
    protected function getClassName($value)
    {
        $value = str_replace('/', '\\', $value);
        $value = explode('\\', $value);

        return array_pop($value);
    }
}
