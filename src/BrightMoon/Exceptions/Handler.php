<?php

namespace BrightMoon\Exceptions;

use BrightMoon\Support\Facades\Route;
use Exception;
use Throwable;

class Handler
{
    /**
     * Đối tượng xử lý lỗi.
     *
     * @var \Throwable
     */
    private $exception;

    /**
     * Khởi tạo đối tượng.
     *
     * @param  Throwable|null  $e
     * @return void
     */
    public function __construct(Throwable $e = null)
    {
        $this->exception = is_null($e) ? new Exception('Có lỗi xảy ra') : $e;
    }

    public function render($file = null, $line = null)
    {
        if (env('APP_DEBUG') === 'false') {
            $message = '404! Yêu cầu của bạn không được tìm thấy.';
            $code = '404';
            include 'resources/BrightMoonErrorView.php';
            die();
        } else {
            $message = $this->exception->getMessage();
            $file ??= $this->exception->getFile();
            $line ??= $this->exception->getLine();
            $class = get_class($this->exception);
            $traces = $this->exception->getTrace();
            $contentFile = $this->readFileException($file, $line);
            $linkToEditor = $this->getLinkToEditor($file, $line);
            $request = request();
            $currentRoute = Route::getRouteByName();
            include 'resources/BrightMoonExceptionView.php';
            die();
        }
    }

    /**
     * Đọc nội dung file lỗi.
     *
     * @param  string  $filePath
     * @param  int  $line
     * @return array
     */
    private function readFileException($filePath, $line)
    {
        $result = [];
        foreach (file($filePath) as $lineCode => $contentCode) {
            if ($lineCode + 1 > $line - 15 && $lineCode + 1 < $line + 15) {
                $result[] = ['line' => $lineCode + 1, 'content' => $this->beautifyContent($contentCode)];
            }
        }

        return $result;
    }

    private function beautifyContent($content)
    {
        if (! empty($content) && ! preg_match('/\/\*\*|\*[\s\n]+|\/\/|\*\//', $content)) {
            $content = preg_replace(
                '/(protected|public|private|const|class|extends|use|namespace) ([A-Z]+[a-zA-Z0-9\\\_]+)*/',
                '<span class="hightlight-keyword">$1</span> <span class="hightlight-title">$2</span>',
                $content
            );

            $content = preg_replace(
                '/(function) ([a-z0-9\\\_]+)/i',
                '<span class="hightlight-keyword">$1</span> <span class="hightlight-name">$2</span>',
                $content
            );
            
            $content = preg_replace(
                '/([A-Z]+[a-zA-Z0-9\\\_]+) (\$[a-zA-Z0-9\\\_]+)/',
                '<span class="hightlight-title">$1</span> $2',
                $content
            );
            
            $content = preg_replace(
                '/(return|throw) /',
                '<span class="hightlight-result">$1</span> ',
                $content
            );
            
            $content = preg_replace(
                '/(new) ([a-zA-Z0-9\$\\\_]+)/',
                '<span class="hightlight-create">$1</span> <span class="hightlight-title">$2</span>',
                $content
            );
            
            $content = preg_replace(
                '/([a-zA-Z0-9\$\\\_]+)\(([^\(^\)]*)\)/',
                '<span class="hightlight-name">$1</span>($2)',
                $content
            );
            
            $content = preg_replace(
                '/([A-Z\\]+[a-zA-Z0-9\\\_]+)::([a-zA-Z0-9_\$]+)/i',
                '<span class="hightlight-title">$1</span>::<span class="hightlight-keyword">$2</span>',
                $content
            );
        }

        return $content;
    }

    private function getLinkToEditor($file, $line)
    {
        $editor = config('error.editor', 'vscode');

        switch ($editor) {
            case 'atom':
                return 'atom://core/open/file?filename=' . $file . '&line=' . $line;
            case 'phpstorm':
                return 'phpstorm://open?file=' . $file . '&line=' . $line;
            case 'subl':
                return 'subl://open?url=file://' . $file . '&line=' . $line;
            case 'vscode':
            default:
                return 'vscode://file/' . $file . ':' . $line;
        }
    }
}
