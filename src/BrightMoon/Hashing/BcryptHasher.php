<?php

namespace BrightMoon\Hashing;

use BrightMoon\Contracts\Hasher;

class BcryptHasher implements Hasher
{
    /**
     * Hệ số mặc định.
     *
     * @var int
     */
    protected $rounds = 10;

    public function __construct(array $options = [])
    {
        $this->rounds = $options['rounds'] ?? $this->rounds;
    }

    /**
     * Tạo chuỗi băm.
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);

        if ($hash === false) {
            throw new RuntimeException('Không hỗ trợ bcrypt hashing.');
        }

        return $hash;
    }

    /**
     * Kiểm tra mật khẩu có khớp không.
     *
     * @param  string  $plainText
     * @param  string  $hashedPassword
     * @return bool
     */
    public function check($plainText, $hashedPassword)
    {
        return password_verify($plainText, $hashedPassword);
    }

    /**
     * Kiểm tra xem hàm băm đã cho có được băm bằng các tùy chọn đã cho không.
     *
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
    }

    /**
     * Lấy thông tin của giá trị băm đã cho.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    /**
     * Tách phần hệ số trong mảng options ra.
     *
     * @param  array  $options
     * @return string
     */
    private function cost(array $options = [])
    {
        return $options['rounds'] ?? $this->rounds;
    }
}
