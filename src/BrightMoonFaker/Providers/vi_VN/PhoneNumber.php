<?php

namespace BrightMoonFaker\Providers\vi_VN;

class PhoneNumber
{
    /**
     * Danh sách đầu số điện thoại các nhà mạng.
     *
     * @var array
     */
    protected $code = [
        86, 96, 97, 98, 32, 33, 34, 35, 36, 37, 38, 39, // Viettel
        88, 91, 94, 83, 84, 85, 81, 82, // Vinaphone
        89, 93, 79, 77, 76, 78, // Mobifone
        92, 56, 58, 59, 99,
    ];

    protected $format = [
        '0[a] ### ####',
        '(0[a]) ### ####',
        '0[a]-###-####',
        '(0[a])###-####',
        '+84-[a]-###-####',
        '(+84)([a])###-####',
        '+84-[a]-###-####',
    ];
}
