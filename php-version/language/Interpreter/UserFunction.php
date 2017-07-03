<?php

namespace Interpreter;


class UserFunction extends XValue
{
    protected $name;
    protected $code;

    /**
     * UserFunction constructor.
     * @param $name
     * @param $code
     */
    public function __construct(XValue $name, XValue $code)
    {
        $this->name = $name;
        $this->code = $code;

        parent::__construct($name);
    }

    /**
     * @return \FunctionCode
     */
    public function getCode()
    {
        return $this->code->getValue();
    }

    public function getArgumentsCount()
    {
        return $this->getCode()->getArgumentsCount();
    }

    public function getName()
    {
        return $this->name;
    }

}