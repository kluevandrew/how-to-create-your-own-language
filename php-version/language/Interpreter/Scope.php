<?php


namespace Interpreter;


class Scope
{
    protected $scope = [];

    public function get($key)
    {
        return $this->scope[$key];
    }

    public function has($key): bool
    {
        return isset($this->scope[$key]);
    }


    public function set($key, $value)
    {
        return $this->scope[$key] = $value;
    }

}