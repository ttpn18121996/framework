<?php

namespace BrightMoon\Console;

class Route
{
    /**
     * Đối số truyền khi gõ lệnh cmd.
     *
     * @var string
     */
    private $arguments;

    public function __construct($arguments = '')
    {
        $this->arguments = $arguments;
    }

    /**
     * Mô tả
     *
     * @param  $arg
     * @return mixed
     */
    public function handle()
    {
        # code...
    }
}
