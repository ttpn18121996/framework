<?php

namespace BrightMoon\Http;

use Closure;
use BrightMoon\Support\Arr;
use BrightMoon\Support\Str;

class Request
{
    /**
     * Danh sách thông tin server.
     *
     * @var array
     */
    public $server = [];

    /**
     * Danh sách request input gửi lên (POST, PUT, PATCH).
     *
     * @var array
     */
    public $request = [];

    /**
     * Danh sách các query string gửi lên.
     *
     * @var array
     */
    public $query = [];

    /**
     * Danh sách request header.
     *
     * @var array
     */
    public $headers = ['header' => [], 'cacheControl' => []];

    /**
     * Phương thức request.
     *
     * @var string
     */
    protected $method;
    public $session;
    public $files = [];
    private $verbs = ['POST', 'PUT', 'PATCH'];

    /**
     * Khởi tạo đối tượng Request.
     *
     * @return void
     */
    public function __construct()
    {
        $this->bootstrapSelf();
    }

    /**
     * Thực hiện thiết lập cài đặt cho request ban đầu.
     *
     * @return void
     */
    public function bootstrapSelf()
    {
        $this->setDataServer();

        $this->setDataHeader();

        $strRequestInput = urldecode(file_get_contents('php://input'));
        parse_str($strRequestInput, $requestInput);

        $this->method = $this->server('REQUEST_METHOD');

        if (
            isset($requestInput['_method'])
            && in_array($requestInput['_method'], ['PUT', 'PATCH', 'DELETE'])
            && Str::upper($this->method) == 'POST'
        ) {
            $this->method = $requestInput['_method'];
        }

        if (in_array($this->method, $this->verbs)) {
            foreach($requestInput as $key => $value) {
                $this->{$key} = $value;
            }
        }

        $this->query = queryStringToArray($this->server['QUERY_STRING'] ?? '');
    }

    private function setDataServer()
    {
        foreach ($_SERVER as $key => $value) {
            $this->server[$key] = $value;
        }
    }

    /**
     * Lấy dữ liệu thiết lập headers cho request.
     *
     * @return void
     */
    private function setDataHeader()
    {
        foreach (getallheaders() as $name => $value) {
            $this->headers['header'][$name] = $value;
        }

        if (isset($this->server['HTTP_CACHE_CONTROL'])) {
            if (strpos('=', $this->server['HTTP_CACHE_CONTROL']) !== false) {
                $this->headers['cacheControl'] = [
                    explode(
                        '=',
                        $this->server['HTTP_CACHE_CONTROL']
                    )[0] => explode(
                        '=',
                        $this->server['HTTP_CACHE_CONTROL']
                    )[1]
                ];
            } else {
                $this->headers['cacheControl'] = $this->server['HTTP_CACHE_CONTROL'];
            }
        }
    }

    /**
     * Lấy một input từ request.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        $req = $this->all();

        if (array_key_exists($key, $req)) {
            return $req[$key];
        }

        return value($default);
    }

    /**
     * Lấy toàn bộ danh sách input submit lên.
     *
     * @return array
     */
    public function all()
    {
        $result = array_merge($this->query, $this->request);

        return $result;
    }

    /**
     * Lấy tham số query url.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return array
     */
    public function query($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->query;
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return value($default);
    }

    public function cookie($key = null, $default = null)
    {
        $cookies = explode('; ', Arr::get($this->headers, 'header.Cookie', ''));
        $cookies = array_filter($cookies);

        if (empty($cookies)) {
            return value($default);
        }

        $cookies = array_map(fn ($cookie) => explode('=', $cookie), $cookies);
        $cookies = array_combine(array_column($cookies, 0), array_column($cookies, 1));

        if (empty($key)) {
            return $cookies;
        }

        return Arr::get($cookies, $key);
    }

    /**
     * Lấy danh sách các thông số của server.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function server(?string $key = null)
    {
        if (! is_null($key)) {
            $key = Str::upper($key);

            return $this->server[$key];
        }

        return $this->server;
    }

    /**
     * Kiểm tra các giá trị được chỉ định có tồn tại hay không.
     *
     * @param  array|string  $keys
     * @return bool
     */
    public function has($keys)
    {
        if (is_string($keys)) {
            return array_key_exists($keys, $this->all());
        }

        foreach ($keys as $key) {
            if (! array_key_exists($key, $this->all())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kiểm tra một trong các giá trị được chỉ định có tồn tại hay không.
     *
     * @param  array  $keys
     * @return bool
     */
    public function hasAny(array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->all())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra phương thức request.
     *
     * @param  string  $method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->method == $method;
    }

    /**
     * Chỉ lấy các input được chỉ định trong request.
     *
     * @param  array|string  $keys
     * @return array
     */
    public function only($keys)
    {
        return Arr::only($this->all(), $keys);
    }

    /**
     * Loại trừ các input được chỉ định khỏi request.
     *
     * @param  array|string  $keys
     * @return array
     */
    public function except($keys)
    {
        return Arr::except($this->all(), $keys);
    }

    public function url()
    {
        return $this->server('REQUEST_SCHEME') . '://' . $this->server('SERVER_NAME') . $this->server('REQUEST_URI');
    }

    public function method()
    {
        return $this->method;
    }

    /**
     * Thiết lập động giá trị cho thuộc tính.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        if (! isset($this->request[$name])) {
            $this->request[$name] = $value;
        }
    }

    /**
     * Lấy giá trị các thuộc tính động.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->input($name);
    }
}
