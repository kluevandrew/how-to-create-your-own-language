<?php


namespace Interpreter;


/**
 * Class Interpreter\Stack
 */
class Stack
{

    protected $stack = [];

    public function push(XValue $value): int
    {
        return array_push($this->stack, $value);
    }


    public function pop(): XValue
    {
        return array_pop($this->stack);
    }

    public function print()
    {
        echo '[' . implode(', ', array_map(function ($value) {
            if (is_array($value)) {
                return '__ARRAY__';
            }

            if (is_resource($value)) {
                return '__RESOURCE';
            }

            return is_scalar($value) ? $value : get_class($value);
        }, $this->stack)) . ']';
    }

}