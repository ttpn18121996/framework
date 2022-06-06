<?php

namespace BrightMoon\NewConsole\Inputs;

class InputDefinition
{
    protected $arguments = [];
    protected $options = [];

    /**
     * Thêm option.
     *
     * @param  InputArgument[]  $arguments
     * @return mixed
     */
    public function addArguments($arguments)
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }

    /**
     * Thêm từng argument input.
     *
     * @param   $argument
     * @return void
     */
    public function addArgument(InputArgument $argument)
    {
        $this->arguments[] = $argument;
    }

    /**
     * Thêm option.
     *
     * @param  InputOption[]  $options
     * @return mixed
     */
    public function addOptions($options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * Thêm từng option input.
     *
     * @param  InputOption  $option
     * @return void
     */
    public function addOption(InputOption $option)
    {
        $this->options[] = $option;
    }
}
