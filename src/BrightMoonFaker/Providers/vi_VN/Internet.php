<?php

namespace BrightMoonFaker\Providers\vi_VN;

use BrightMoonFaker\Providers\Internet as BaseInternet;

class Internet extends BaseInternet
{
    /**
     * Danh sách tên miền email an toàn (tên miền giả).
     *
     * @var string[]
     */
    protected $safeEmailDomain = ['example.com', 'example.org', 'example.vn', 'example.com.vn', 'example.net',];

    /**
     * Danh sách tên miền email miễn phí.
     *
     * @var string[]
     */
    protected $freeEmailDomain = ['gmail.com', 'yahoo.com', 'hotmail.com'];

    /**
     * Danh sách tên miền phổ biến.
     *
     * @var string[]
     */
    protected $tld = [
        'com', 'biz', 'info', 'net', 'org', 'vn', 'com.vn', 'biz.vn',
        'edu.vn', 'gov.vn', 'net.vn', 'org.vn', 'pro.vn', 'info.vn',
    ];
}
