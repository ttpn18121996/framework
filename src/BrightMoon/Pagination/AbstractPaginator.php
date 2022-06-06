<?php

namespace BrightMoon\Pagination;

abstract class AbstractPaginator
{
    /**
     * Danh sách dữ liệu cho 1 trang.
     *
     * @var \BrightMoon\Support\Collection|array
     */
    protected $items;

    /**
     * Trang hiện tại.
     *
     * @var int
     */
    protected $currentPage = 1;

    /**
     * Giới hạn dữ liệu 1 trang.
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * Kiểm tra còn trang nào không.
     *
     * @var bool
     */
    protected $hasMore;

    /**
     * Danh sách trang hiển thị trong phần phân trang (bao gồm rút gọn phân trang và chèn ...).
     *
     * @var array
     */
    protected $elements;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected $pageParam = 'page';

    /**
     * Đường dẫn dành cho trang kế kết hợp với pageParam.
     *
     * @var string
     */
    protected $path = '/';

    /**
     * @var array
     */
    protected $options;

    /**
     * Giải quyết trang hiện tại.
     *
     * @param  string $pageName
     * @return int
     */
    public static function resolveCurrentPage($pageName = 'page')
    {
        $page = app('request')->input($pageName);

        if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
            return (int) $page;
        }

        return 1;
    }

    /**
     * Lấy đường dẫn tới file view.
     *
     * @param  string  $path
     * @return string
     */
    public function getViewPath($path = '')
    {
        if (empty($path)) {
            return __DIR__."/resources/{$this->fileDefault}";
        }

        $pathView = $pathKey.'/'.$pathFile;

        return base_path(config('view.view_path').'/'.str_replace('.', '/', $path));
    }

    /**
     * Lấy đường dẫn với chuỗi query url.
     *
     * @param  array  $queryReplace
     * @return string
     */
    protected function getPathWithQueryString(array $queryReplace = [])
    {
        $path = $this->path;
        $queryStrings = request()->query();
        $queryString = http_build_query(array_merge($queryStrings, $queryReplace));
        $path = $path.'?'.$queryString;

        return $path;
    }

    /**
     * Lấy số thứ tự cho từng item theo trang.
     *
     * @param  int  $index
     * @return int
     */
    public function lineNumber($index)
    {
        if (property_exists($this, 'total')) {
            return $index;
        }

        return $index + $this->firstItem();
    }

    /**
     * Lấy số thứ tự item đầu tiên theo trang.
     *
     * @return int
     */
    public function firstItem()
    {
        return count($this->items) > 0 ? $this->perPage * ($this->currentPage - 1) : 0;
    }

    /**
     * Lấy số thứ tự item cuối cùng theo trang.
     *
     * @return int
     */
    public function lastItem()
    {
        return count($this->items) > 0 ? $this->perPage * $this->currentPage : 0;
    }

    /**
     * Hiển thị view phân trang.
     *
     * @param  string $view
     * @param  array $data
     * @return void
     *
     * @throws \BrightMoon\Exception\BrightMoonException
     */
    abstract public function render($view = '', array $data = []);
}
