<?php

namespace Interpreter;


class NativeFunction extends XValue
{
    protected $name;
    protected $argumentsCount;
    /**
     * @var callable
     */
    private $executor;

    public function __construct($name, callable $executor, $argumentsCount = -1)
    {
        $this->name = $name;
        $this->argumentsCount = $argumentsCount;
        $this->executor = $executor;

        parent::__construct($name);
    }

    public function execute(array $arguments)
    {
        $arguments = array_map(function (XValue $argument) {
            return $argument->getValue();
        }, $arguments);

        return call_user_func_array($this->executor, $arguments);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getArgumentsCount(): int
    {
        return $this->argumentsCount;
    }


}