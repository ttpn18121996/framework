<?php

namespace BrightMoon;

use BrightMoon\Support\Facades\View;
use BrightMoon\Support\Str;

abstract class Controller
{
    /**
     * Danh sách các middleware của Controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Trả về giao diện mặc định theo đường dẫn views/controller/action.
     *
     * @param array $data
     * @return \BrightMoon\Support\Facades\View
     */
    public function view($data = [])
    {
        $folderName = Str::of(class_basename(get_class($this)))->lower()->replace('controller', '');
        $fileName = debug_backtrace()[1]['function'];
        $viewPath = $folderName.'.'.$fileName;

        return View::make($viewPath, $data);
    }

    /**
     * Thiết lập giao diện mà view sẽ kế thừa.
     *
     * @param string $layout
     * @return $this
     */
    public function extends($layout)
    {
        View::extends($layout);

        return $this;
    }
}
