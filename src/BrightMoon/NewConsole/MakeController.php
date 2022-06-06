<?php

namespace BrightMoon\NewConsole;

use BrightMoon\Support\Str;

class MakeController extends Console
{
    /**
     * Cú pháp nhập lệnh để thực thi.
     *
     * @var string
     */
    public $signature = 'make:controller {name}';

    /**
     * Xử lý khi dùng lệnh.
     *
     * @return mixed
     */
    public function handle()
    {
        # code...
    }

    /**
     * Lấy namespace.
     *
     * @param  string  $arg
     * @return mixed
     */
    protected function getNamespace($value)
    {
        $last = Str::of($value)->replace('/', '\\')->beforeLast('\\');
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
        return Str::of($value)->replace('/', '\\')->afterLast('\\');
    }
}
