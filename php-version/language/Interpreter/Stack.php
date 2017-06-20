<?php


namespace Interpreter;


/**
 * Class Interpreter\Stack
 */
class Stack
{

    protected $stack = [];

    public function push($value): int
    {
        return array_push($this->stack, $value);
    }


    public function pop()
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