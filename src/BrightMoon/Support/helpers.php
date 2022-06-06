<?php

use BrightMoon\Foundation\Application;
use BrightMoon\Foundation\Config;
use BrightMoon\Support\Collection;
use BrightMoon\Support\Dumper;
use BrightMoon\Support\Env;

if (! function_exists('app')) {
    /**
     * Khởi tạo Application / khởi tạo đối tượng.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    function app($abstract = null, $parameters = [])
    {
        $app = Application::getInstance();

        if (is_null($abstract)) {
            return $app;
        }

        return $app->make($abstract, $parameters);
    }
}

if (! function_exists('asset')) {
    function asset($path = '')
    {
        # code...
    }
}

if (! function_exists('base_path')) {
    /**
     * Lấy đường dẫn thư mục gốc.
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (! function_exists('base_url')) {
    /**
     * Lấy đường dẫn gốc.
     *
     * @param string $uri
     * @return string
     */
    function base_url($uri = '')
    {
        $protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ? 'https' : 'http';
        $root  = "{$protocol}://".$_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        return ! empty($uri) ? $root."/{$uri}" : $root;
    }
}

if (! function_exists('class_basename')) {
    /**
     * Lấy tên của object / class không chứa namespace.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('collect')) {
    /**
     * Tạo đối tượng Collection từ giá trị truyền vào.
     *
     * @param mixed $value
     * @return \BrightMoon\Support\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (! function_exists('config')) {
    /**
     * Lấy các giá trị trong cấu hình hoặc khởi tạo Config.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = '', $default = null)
    {
        $config = Config::getInstance();

        if (empty($key)) {
            return $config;
        }

        return $config->getConfig($key, $default);
    }
}

if (! function_exists('dd')) {
    /**
     * Xuất kết quả trả về của các biến.
     *
     * @param  mixed $args
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }

        die(1);
    }
}

if (! function_exists('dump')) {
    /**
     * Xuất kết quả trả về của các biến.
     *
     * @param  mixed $args
     * @return void
     */
    function dump(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }
    }
}

if (! function_exists('env')) {
    /**
     * Lấy dữ liệu cấu hình trong Env.
     *
     * @param string $key
     * @param string|null $default
     * @return string
     */
    function env($key, $default = null)
    {
        $env = Env::getInstance();
        return $env->get($key, $default);
    }
}

if (! function_exists('now')) {
    function now($timezone = null)
    {
        if (is_null($timezone)) {
            $timezone = config('app.timezone');
        }

        return \Carbon\Carbon::now($timezone);
    }
}

if (! function_exists('queryStringToArray')) {
    /**
     * Chuyển chuỗi URL hoặc query string sang mảng chứa các tham số.
     *
     * @param  string  $string
     * @return array
     */
    function queryStringToArray($string = '')
    {
        if (empty($string)) {
            return [];
        }

        if (filter_var($string, FILTER_VALIDATE_URL)) {
            $string = (string) parse_url($string, PHP_URL_QUERY);
        }

        if (preg_match('/(.+=.*){1,}/', $string)) {
            parse_str($string, $result);
        } else {
            $result = [];
        }

        return $result;
    }
}

if (! function_exists('request')) {
    /**
     * Lấy thông tin Request.
     *
     * @return \BrightMoon\Http\Request
     */
    function request()
    {
        return app('request');
    }
}

if (! function_exists('response')) {
    /**
     * Lấy thông tin Response.
     *
     * @param  mixed  $data
     * @return \BrightMoon\Http\Response
     */
    function response($data = null)
    {
        return app(\BrightMoon\Http\Response::class, compact('data'));
    }
}

if (! function_exists('value')) {
    /**
     * Trả về giá trị mặc định của giá trị đã cho.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (! function_exists('view')) {
    /**
     * Khởi tạo view.
     *
     * @param string $path
     * @param array $data
     * @return string
     */
    function view($path, $data = [])
    {
        return \BrightMoon\Support\Facades\View::make($path, $data);
    }
}

if (! function_exists('windows_os')) {
    /**
     * Xác định xem môi trường hiện tại có dựa trên Windows hay không.
     *
     * @return bool
     */
    function windows_os()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}
