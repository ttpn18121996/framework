<?php

namespace BrightMoon\Pagination;

use ArrayAccess;
use ArrayIterator;
use BrightMoon\Exceptions\BrightMoonException;
use BrightMoon\Support\Collection;
use BrightMoon\Contracts\Support\Arrayable;
use BrightMoon\Contracts\Support\Jsonable;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Paginator extends AbstractPaginator implements
    Arrayable,
    ArrayAccess,
    Countable,
    IteratorAggregate,
    Jsonable,
    JsonSerializable
{
    /**
     * Trang trước.
     *
     * @var int
     */
    protected $prevPage = 1;

    /**
     * Trang kế.
     *
     * @var int
     */
    protected $nextPage = 1;

    /**
     * Tổng số trang.
     *
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    private $onEachSide = 3;

    /**
     * Tên file view phân trang mặc định.
     *
     * @var string
     */
    protected $fileDefault = 'pagination';

    /**
     * Khởi tạo đối tượng phân trang Paginator.
     *
     * @param  \BrightMoon\Support\Collection|array  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $option (path, pageParam)
     * @return void
     */
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->total = (int) $total;
        $this->perPage = (int) $perPage;
        $this->onEachSide = config('constant.pagination.page_on_each_side', 3);
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->nextPage = $this->setNextPage();
        $this->prevPage = $this->setPrevPage();

        $this->items = $items instanceof Collection ? $items : new Collection($items);
        $this->elements = $this->elements();
    }

    /**
     * Thiết lập trang hiện tại.
     *
     * @param  int  $currentPage
     * @return int
     */
    private function setCurrentPage($currentPage)
    {
        $currentPage = static::resolveCurrentPage($this->pageParam);

        if ($currentPage > $this->total) {
            $currentPage = $this->total();
        }

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        return $currentPage;
    }

    /**
     * Thiết lập trang kế tiếp.
     *
     * @return int
     */
    private function setNextPage()
    {
        if ($this->currentPage < $this->total) {
            return $this->currentPage + 1;
        }

        return $this->total;
    }

    /**
     * Thiết lập trang trước.
     *
     * @return int
     */
    private function setPrevPage()
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }

        return 1;
    }

    /**
     * Xác nhận xem còn trang kế tiếp hay không.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->currentPage() < $this->total;
    }

    /**
     * Xác nhận xem có đủ item để tiến hành phân trang không.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->total > 1;
    }

    /**
     * Lấy đường dẫn một trang cụ thể.
     *
     * @param  int  $page
     * @return string
     */
    public function url($page)
    {
        return $this->getPathWithQueryString([
            "{$this->pageParam}" => $page,
        ]);
    }

    /**
     * Lấy đường dẫn trang trước.
     *
     * @return mixed
     */
    public function prevPageUrl()
    {
        return $this->getPathWithQueryString([
            "{$this->pageParam}" => $this->prevPage,
        ]);
    }

    /**
     * Lấy đường dẫn trang kế.
     *
     * @return mixed
     */
    public function nextPageUrl()
    {
        return $this->getPathWithQueryString([
            "{$this->pageParam}" => $this->nextPage,
        ]);
    }

    /**
     * Lấy url trang cuối.
     *
     * @return string
     */
    public function lastPageUrl()
    {
        return $this->getPathWithQueryString([
            "{$this->pageParam}" => $this->total,
        ]);
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
    public function render($view = '', array $data = [])
    {
        $data = array_merge($data, [
            'paginator' => $this
        ]);

        extract($data, EXTR_OVERWRITE);

        $pathView = $this->getViewPath($view);

        try {
            if (file_exists($pathView.'.php')) {
                include $pathView.'.php';
            } else {
                throw new BrightMoonException("Không tìm thấy view [{$path}]");
            }
        } catch (BrightMoonException $ex) {
            $ex->render();
            die();
        }
    }

    /**
     * Xuất mảng phân thông tin phân trang.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage,
            'items' =>  $this->items->toArray(),
            'prev_page' => $this->prevPage,
            'next_page' => $this->nextPage,
            'total' => $this->total,
            'path' => $this->path,
            'prev_page_url' => $this->prevPageUrl(),
            'next_page_url' => $this->nextPageUrl(),
            'last_page_url' => $this->lastPageUrl(),
            'page_param' => $this->options['pageParam'] ?? 'page',
            'elements' => $this->elements(),
        ];
    }

    /**
     * Lấy danh sách trang hiển thị trong phần phân trang (bao gồm rút gọn phân trang và chèn ...).
     *
     * @return mixed
     */
    public function elements()
    {
        $window = $this->onEachSide + 4;
        $elements = [
            'first' => range(1, $this->total),
            'slider' => null,
            'last' => null,
        ];
        $first = [1, 2];
        $last = [$this->total - 1, $this->total];
        
        if ($this->total <= 1) {
            $elements = [
                'first' => null,
                'slider' => null,
                'last' => null,
            ];
        } elseif ($this->total > ($this->onEachSide * 2) + 8) {
            if ($this->currentPage <= $window) {
                $elements['first'] = range(1, $window + $this->onEachSide);
                $elements['last'] = $last;
            } elseif ($this->currentPage > ($this->total - $window)) {
                $elements['first'] = $first;
                $elements['last'] = range($this->total - $window - $this->onEachSide + 1, $this->total);
            }  else {
                $elements['first'] = $first;
                $elements['slider'] = range($this->currentPage - $this->onEachSide, $this->currentPage + $this->onEachSide);
                $elements['last'] = $last;
            }
        }

        $this->elements = array_filter([
            $elements['first'],
            is_array($elements['slider']) ? '...' : null,
            $elements['slider'],
            is_array($elements['last']) ? '...' : null,
            $elements['last']
        ]);

        return $this->elements;
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
        }, $this->toArray());
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return $this->items->getIterator();
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
    public function offsetGet($key): mixed
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

    /**
     * Xử lý khi gọi một phương thức động của paginator.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (property_exists($this, $method)) {
            return $this->{$method};
        }

        return null;
    }
}
