<?php

namespace BrightMoon\Console;

class Executor
{
    protected $arguments = [];

    protected $argumentCount = 0;

    public function __construct(array $arguments, $argumentCount)
    {
        $this->arguments = $arguments;
        $this->argumentCount = $argumentCount;
    }
}
