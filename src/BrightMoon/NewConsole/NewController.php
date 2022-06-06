<?php

namespace BrightMoon\NewConsole;

class NewController extends Console
{
    /**
     * Cú pháp thực hiện lệnh command.
     *
     * @var string
     */
    protected $signature = 'make:controller {name : name of controller} {--r|resource : option resource}';

    private $namespace = 'App\Controllers';

    /**
     * Xử lý lệnh command.
     *
     * @return void
     */
    public function handle()
    {
        $contentFile = file_get_contents(__DIR__.'/resources/controller.txt');
    }
}
