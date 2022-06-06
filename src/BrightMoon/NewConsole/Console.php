<?php

namespace BrightMoon\NewConsole;

use BrightMoon\NewConsole\Inputs\InputDefinition;
use BrightMoon\NewConsole\Inputs\InputArgument;
use BrightMoon\NewConsole\Inputs\InputOption;
use BrightMoon\Support\Str;

abstract class Console
{
    /**
     * Tên của command.
     *
     * @var string
     */
    protected $name;

    /**
     * Cú pháp của command.
     *
     * @var string
     */
    protected $signature;

    /**
     * Đối tượng định nghĩa cho các input của command.
     *
     * @var \BrightMoon\NewConsole\Inputs\InputDefinition
     */
    protected $definition;

    public function __construct()
    {
        $this->definition = new InputDefinition;
        $this->configureDefinition();
    }

    /**
     * Cấu hình cho Definition.
     *
     * @return void
     */
    private function configureDefinition()
    {
        $this->setName();

        [$arguments, $options] = $this->parse();

        $this->definition->addArguments($arguments);
        $this->definition->addOptions($options);
    }

    /**
     * Phân tích chuỗi signature và lấy danh sách các tham số để định nghĩa.
     *
     * @return array[]
     */
    protected function parse()
    {
        if (preg_match_all('/\{\s*(.*?)\s*\}/', $this->signature, $matches)) {
            if (count($matches[1])) {
                return $this->parameters($matches[1]);
            }
        }

        return [[], []];
    }

    /**
     * Thiết lập tên cho command.
     *
     * @param   
     * @return void
     */
    public function setName()
    {
        if (! preg_match('/[^\s]+/', $this->signature, $matches)) {
            throw new InvalidArgumentException('Không thể xác định tên lệnh từ signature.');
        }

        $this->name = $matches[0];
    }

    /**
     * Phân tích các tham số và phân loại chúng argument / option.
     *
     * @param  array  $params
     * @return array[]
     */
    protected function parameters(array $params)
    {
        $options = [];
        $arguments = [];

        if (count($params) > 1) {
            foreach ($params as $param) {
                if (preg_match('/-{2,}(.*)/', $param, $match)) {
                    $options[] = $this->parseOption($match[1]);
                } else {
                    $arguments[] = $this->parseArgument($param);
                }
            }
        }

        return [$arguments, $options];
    }

    /**
     * Phân tích signature để lấy option.
     *
     * @param  string $token
     * @return string
     */
    protected function parseOption($token)
    {
        [$token, $description] = $this->extractDescription($token);

        $matches = preg_split('/\s*\|\s*/', $token, 2);

        if (isset($matches[1])) {
            $shortcut = $matches[0];
            $token = $matches[1];
        } else {
            $shortcut = null;
        }

        return new InputOption($token, $shortcut, InputOption::VALUE_NONE, $description);
    }

    /**
     * Phân tích chuỗi để lấy argument.
     *
     * @param  string  $token
     * @return string
     */
    protected function parseArgument($token)
    {
        [$token, $description] = $this->extractDescription($token);

        $matches = preg_split('/\s*\|\s*/', $token, 2);

        if (isset($matches[1])) {
            $shortcut = $matches[0];
            $token = $matches[1];
        } else {
            $shortcut = null;
        }

        return new InputArgument($token, InputArgument::VALUE_NONE, $description);
    }

    /**
     * Phân tích cú pháp thành các phân đoạn token và mô tả của nó.
     *
     * @param  string  $token
     * @return array
     */
    protected function extractDescription($token)
    {
        $parts = preg_split('/\s+:\s+/', trim($token), 2);

        return count($parts) === 2 ? $parts : [$token, ''];
    }

    /**
     * Lấy giá trị argument.
     *
     * @param  string  $name
     * @return string|array
     */
    public function getArgument($name)
    {
        // code...
    }

    /**
     * Xử lý lệnh.
     *
     * @return void
     */
    abstract public function handle();
}
