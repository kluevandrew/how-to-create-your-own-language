<?php


namespace Interpreter;


class Scope
{
    /**
     * @var Scope
     */
    protected $parent;

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * Scope constructor.
     *
     * @param Scope $parent
     */
    public function __construct(Scope $parent = null)
    {
        $this->parent = $parent;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->scope)) {
            return $this->scope[$key];
        }

        if ($this->parent) {
            return $this->parent->get($key);
        }

        return null;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->scope) || ($this->parent && $this->parent->has($key));
    }

    public function set($key, XValue $value)
    {
        return $this->scope[$key] = $value;
    }

    public function all()
    {
        return $this->scope;
    }

}