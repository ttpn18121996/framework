<?php

namespace BrightMoon\Console;

class Controller extends Console
{
    /**
     * Nội dung file.
     *
     * @var string
     */
    private $contentFile;

    /**
     * Namespace của đối tượng được tạo.
     *
     * @var string
     */
    protected $namespace = 'App\\Controllers';

    /**
     * Đối số truyền khi gõ lệnh cmd.
     *
     * @var string
     */
    private $arguments;

    protected $signature = 'make:controller {name}';

    /**
     * Khởi tạo đối tượng Controller Console.
     *
     * @param  string  $arguments
     * @return void
     */
    public function __construct($arguments = '', $basePath = '')
    {
        $this->arguments = $arguments;
        $this->contentFile = file_get_contents(__DIR__.'/resources/controller.stub');
        $this->basePath = $basePath;
    }

    /**
     * Xử lý lệnh.
     *
     * @return void
     */
    public function handle()
    {
        $namespace = $this->getNamespace($this->arguments);
        $className = $this->getClassName($this->arguments);
        $fileLocation = $this->basePath.'/app/Controllers/'.str_replace('\\', '/', $this->arguments).'.php';
        $dir = str_replace($className.'.php', '', $fileLocation);

        if (! file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = fopen($fileLocation, 'w');
        $content = str_replace('{{ NAMESPACE }}', $namespace, $this->contentFile);
        $content = str_replace('{{ CLASS }}', $className, $content);
        fwrite($file, $content);
        fclose($file);
        
        if (file_exists($fileLocation)) {
            echo 'Controller created successfully';
        }
    }
}
