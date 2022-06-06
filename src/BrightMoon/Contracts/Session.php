<?php

namespace BrightMoon\Contracts;

interface Session
{
    /**
     * Khởi động session.
     *
     * @return bool
     */
    public function start();

    public function put($key, $value = null);
}
