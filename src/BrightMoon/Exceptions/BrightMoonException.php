<?php

namespace BrightMoon\Exceptions;

use ErrorException;
use Throwable;

class BrightMoonException extends ErrorException
{
    /**
     * Khởi tạo đối tượng.
     *
     * @param  string  $message
     * @param  int  $code
     * @return void
     */
    public function __construct($message = 'Unknown exception', $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * Trả về chuỗi thông báo khi chuyển kiểu thành chuỗi.
     *
     * @return string
     */
    public function __toString()
    {
        return "[{$this->code}]: {$this->message}\n";
    }
}
