<?php

namespace Interpreter;

class XValue
{
    protected $value;

    /**
     * XValue constructor.
     *
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        return gettype($this->value);
    }

}