<?php

namespace BrightMoon\Contracts\Support;

interface Jsonable
{
    /**
     * Chuyển đối tượng thành kiểu JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
