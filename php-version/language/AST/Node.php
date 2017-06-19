<?php

namespace AST;

abstract class Node
{
    abstract public function __toString();

    protected function toString(array $arguments)
    {
        return '<' . get_class($this). '>' . PHP_EOL . implode(PHP_EOL, $arguments);
    }
}