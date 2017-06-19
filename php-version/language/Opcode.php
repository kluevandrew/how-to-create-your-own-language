<?php


class Opcode
{
    const LOAD_CONST = 'LOAD_CONST';
    const STORE_FAST = 'STORE_FAST';
    const LOAD_FAST = 'LOAD_FAST';
    const BINARY_ADD = 'BINARY_ADD';
    const BINARY_MINUS = 'BINARY_MINUS';
    const BINARY_MULTIPLY = 'BINARY_MULTIPLY';
    const BINARY_DIVIDE = 'BINARY_DIVIDE';
    const BINARY_POW = 'BINARY_POW';
    const BINARY_INSTANCE_OF = 'BINARY_INSTANCE_OF';
    const CALL_FUNCTION = 'CALL_FUNCTION';
    const LOAD_GLOBAL = 'LOAD_GLOBAL';

    protected $type;

    protected $value;

    /**
     * Opcode constructor.
     *
     * @param string $type
     * @param mixed $value
     */
    public function __construct(string $type, $value = null)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }


}