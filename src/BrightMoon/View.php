<?php

namespace BrightMoon;

use BrightMoon\Exceptions\BrightMoonViewException;
use BrightMoon\Foundation\Config;
use BrightMoon\Pagination\AbstractPaginator;

class View
{
    /**
     * Đường dẫn tới nơi chứa file view.
     *
     * @var string
     */
    public $viewPath;

    /**
     * Dữ liệu truyền qua view.
     *
     * @var array
     */
    public $viewBag = [];

    /**
     * Nội dung view.
     *
     * @var string
     */
    public $viewContent;

    /**
     * Tên master layout mà view sẽ kế thừa.
     *
     * @var string
     */
    protected $layout;

    /**
     * Thông tin cấu hình cho view.
     *
     * @var array
     */
    protected $configs;

    /**
     * Tên section hiện tại.
     *
     * @var string
     */
    protected $currentSection;

    /**
     * Khởi tạo đối tượng xử lý view.
     *
     * @return void
     */
    public function __construct()
    {
        $this->configs = Config::getInstance()->getConfig('constant.view');
        $this->viewPath = $this->configs['view_path'] ?? 'app/Views';
    }

    /**
     * Thiết lập giao diện mà view sẽ kế thừa.
     *
     * @param string $layout
     * @return void
     */
    public function extends($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Tạo view.
     *
     * @param string $path
     * @param array $data
     * @param string $controllerName
     * @return mixed
     *
     * @throws \BrightMoon\Exceptions\BrightMoonViewException
     */
    public function make($path, array $data = [])
    {
        $this->viewBag = $data;
        $this->setViewContent($path);

        if (! is_null($this->layout)) {
            $pathLayout = base_path($this->viewPath . '/' . str_replace('.', '/', $this->layout));

            if (! file_exists($pathLayout . '.php')) {
                throw new BrightMoonViewException("Không tìm thấy view [{$this->layout}]");
            }

            ob_start();
            include $pathLayout.'.php';

            $renderView = ob_get_clean();

            return $renderView;
        }

        return $this->viewContent['no_layout'];
    }

    /**
     * Thiết lập nội dung cho view.
     *
     * @param string $path
     * @return void
     *
     * @throws \BrightMoon\Exceptions\BrightMoonViewException
     */
    public function setViewContent($path)
    {
        $pathView = base_path($this->viewPath.'/'.str_replace('.', '/', $path));

        if (count($this->viewBag) > 0) {
            extract($this->viewBag, EXTR_OVERWRITE);
        }

       if (! file_exists($pathView.'.php')) {
           throw new BrightMoonViewException("Không tìm thấy view [{$path}]");
        }

        ob_start();
        include $pathView.'.php';

        if (is_null($this->layout)) {
            $this->viewContent['no_layout'] = ob_get_clean();
        }

        ob_get_length() && ob_end_clean();
    }

    /**
     * Định nghĩa thành phần cho view.
     *
     * @param string $key
     * @param string $content
     * @return void
     */
    public function section($key, $content = '')
    {
        if ($content != '') {
            $this->viewContent[$key] = $content;
        } else {
            ob_start();
        }

        $this->currentSection = $key;
    }

    /**
     * Kết thúc định nghĩa thành phần cho view.
     *
     * @param string $key
     * @return void
     */
    public function endSection()
    {
        $this->viewContent[$this->currentSection] = ob_get_clean();
    }

    /**
     * Hiển thị nội dung view.
     *
     * @param string $key
     * @return string
     */
    public function render($key)
    {
        return $this->viewContent[$key] ?? '';
    }

    /**
     * Tải trang con lên trang chính.
     *
     * @param  string  $path
     * @param  array  $data
     * @return void
     */
    public function partial($path, array $data = [])
    {
        include base_path($this->viewPath.'/'.str_replace('.', '/', $path).'.php');
    }

    /**
     * Hiển thị đánh số trang.
     * 
     * @param  \BrightMoon\Pagination\AbstractPaginator  $paginator
     * @param  string  $view
     * @param  array  $data
     * @return void
     */
    public function paging(AbstractPaginator $paginator, $view = '', array $data = [])
    {
        $paginator->render($view, $data);
    }
}
