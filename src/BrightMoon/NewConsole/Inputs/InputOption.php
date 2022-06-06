<?php

namespace BrightMoon\NewConsole\Inputs;

class InputOption
{
    private $name;
    private $shortcut;
    private $mode;
    private $description;

    const VALUE_NONE = 1;

    /**
     * Khởi tạo đối tượng.
     *
     * @param  string  $name
     * @param  string  $shortcut
     * @param  string  $mode
     * @param  string  $description
     * @return void
     */
    public function __construct($name, $shortcut, $mode, $description)
    {
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->mode = $mode;
        $this->description = $description;
    }
}
