<?php

namespace BrightMoon\Contracts;

interface Hasher
{
    /**
     * Tạo chuỗi băm.
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make($value, array $options = []);

    /**
     * Kiểm tra mật khẩu có khớp không.
     *
     * @param  string  $plainText
     * @param  string  $hashedPassword
     * @return bool
     */
    public function check($plainText, $hashedPassword);

    /**
     * Kiểm tra xem hàm băm đã cho có được băm bằng các tùy chọn đã cho không.
     *
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = []);

    /**
     * Lấy thông tin của giá trị băm đã cho.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue);
}
