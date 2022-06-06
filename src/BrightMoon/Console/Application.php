<?php

namespace BrightMoon\Console;

class Application
{
    /**
     * Khởi tạo đối tượng xử lý command.
     *
     * @param  array  $argvx
     * @param  int  $argc
     * @return mixed
     */
    public function __construct(array $argv, $argc, $basePath = '')
    {
        $this->argv = $argv;
        $this->count = $argc;
        $this->basePath = $basePath;
    }

    /**
     * Tạo file.
     *
     * @return mixed
     */
    public function make()
    {
        if ($this->count == 1) {
            return $this;
        } elseif ($this->count == 3) {
            $option = $this->handleOptions($this->argv[1]);
            $arguments = $this->argv[2];

            switch ($option) {
                case 'controller':
                    return new Controller($arguments, $this->basePath);
                case 'model':
                    return new Model($arguments, $this->basePath);
                case 'route':
                    return new Route($arguments);
                default:
                    return $option;
            }
        }
    }

    /**
     * Xử lý option của lệnh.
     *
     * @param  string  $options
     * @return string
     */
    private function handleOptions($options)
    {
        if (strpos($options, 'make:') !== false) {
            return explode(':', $options)[1];
        }

        return '';
    }

    /**
     * Xử lý lệnh.
     *
     * @return string
     */
    public function handle()
    {
        echo include __DIR__.'/resources/help.php';
    }
}
