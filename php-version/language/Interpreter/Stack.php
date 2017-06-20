<?php


namespace Interpreter;


/**
 * Class Interpreter\Stack
 */
class Stack
{

    protected $stack = [];

    public function push($value) : int
    {
        return array_push($this->stack, $value);
    }


    public function pop()
    {
        return array_pop($this->stack);
    }

    public function print()
    {
        echo '[' . implode(', ', $this->stack) . ']';
    }

}