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

class SimplePaginator extends AbstractPaginator implements
    Arrayable,
    ArrayAccess,
    Countable,
    IteratorAggregate,
    Jsonable,
    JsonSerializable
{
    /**
     * Tên file view phân trang mặc định.
     *
     * @var string
     */
    protected $fileDefault = 'simple-pagination';

    /**
     * Khởi tạo đối tượng phân trang Paginator.
     *
     * @param  \BrightMoon\Support\Collection|array  $items
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $option (path, pageParam)
     * @return void
     */
    public function __construct($items, $perPage, $currentPage = null, array $options = [])
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = (int) $perPage;
        $this->currentPage = static::resolveCurrentPage($this->pageParam);

        $this->setItems($items);
    }

    /**
     * Thiết lập items cho trang.
     *
     * @param  \BrightMoon\Support\Collection|array  $items
     * @return void
     */
    private function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->items->count() > $this->perPage;

        $this->items = $this->items->slice(0, $this->perPage);
    }

    /**
     * Xác nhận xem còn trang kế tiếp hay không.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->hasMore;
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
     * Lấy đường dẫn trang trước.
     *
     * @return mixed
     */
    public function prevPageUrl()
    {
        if ($this->currentPage > 1) {
            return $this->getPathWithQueryString([
                "{$this->pageParam}" => $this->currentPage - 1,
            ]);
        }
    }

    /**
     * Lấy đường dẫn trang kế.
     *
     * @return mixed
     */
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->getPathWithQueryString([
                "{$this->pageParam}" => $this->currentPage + 1,
            ]);
        }
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
     * Xuất mảng phân thông tin phân trang.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage,
            'items' =>  $this->items->toArray(),
            'path' => $this->path,
            'prev_page_url' => $this->prevPageUrl(),
            'next_page_url' => $this->nextPageUrl(),
            'page_param' => $this->options['pageParam'] ?? 'page',
        ];
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
