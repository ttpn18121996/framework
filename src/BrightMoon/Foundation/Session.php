<?php

namespace BrightMoon\Foundation;

use BrightMoon\Contracts\Session as BaseSession;
use BrightMoon\Support\Str;

class Session implements BaseSession
{
    protected $id;

    protected $started = false;

    public function __construct($id)
    {
        $this->setId($id);
    }

    public function start()
    {
        $this->loadSession();

        $this->started = true;
    }

    public function loadSession()
    {
        # code...
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id ?? Str::random(32);
    }

    public function put($key, $value = null)
    {
        # code...
    }
}
