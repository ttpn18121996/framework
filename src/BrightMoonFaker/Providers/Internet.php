<?php

namespace BrightMoonFaker\Providers;

use BrightMoon\Support\Str;
use BrightMoonFaker\Faker;

class Internet
{
    public function email($fullName = null, $isSafe = false)
    {
        $fullName ??= app(Faker::class)->name();

        $emailDomain = $isSafe
            ? $this->safeEmailDomain[rand(0, count($this->safeEmailDomain) - 1)]
            : $this->freeEmailDomain[rand(0, count($this->freeEmailDomain) - 1)];

        return Str::of($fullName)
            ->noneUnicode()
            ->snake()
            ->append('.'.rand(1, 999).'@'.$emailDomain)
            ->toString();
    }

    public function safeEmail($fullName = null)
    {
        return $this->email($fullName, true);
    }
}
