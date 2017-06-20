<?php

namespace Interpreter;


class UserFunction
{
    protected $name;
    protected $code;

    /**
     * UserFunction constructor.
     * @param $name
     * @param $code
     */
    public function __construct($name, \FunctionCode $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * @return \FunctionCode
     */
    public function getCode()
    {
        return $this->code;
    }



}