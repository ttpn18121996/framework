<?php

namespace BrightMoon\NewConsole\Inputs;

class InputArgument
{
    protected $name;
    protected $mode;
    protected $description;
    protected $default;

    const VALUE_NONE = 1;

    /**
     * Khởi tạo đối tượng.
     *
     * @param  string  $name
     * @param  int  $mode
     * @param  string  $description
     * @return void
     */
    public function __construct($name, $mode, $description)
    {
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
    }

    /**
     * Lấy tên tham số.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Lấy mô tả tham số.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
