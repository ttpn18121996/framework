<?php

namespace BrightMoon\Support\Facades;

class Session extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'session';
    }
}
