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
    const BOOLEAN_AND = 'BOOLEAN_AND';
    const BOOLEAN_OR = 'BOOLEAN_OR';
    const CALL_FUNCTION = 'CALL_FUNCTION';
    const LOAD_GLOBAL = 'LOAD_GLOBAL';
    const JUMP_IF_FALSE = 'JUMP_IF_FALSE';
    const COMPARE_GT = 'COMPARE_GT';
    const COMPARE_GTE = 'COMPARE_GTE';
    const COMPARE_LT = 'COMPARE_LT';
    const COMPARE_LTE = 'COMPARE_LTE';
    const JUMP = 'JUMP';
    const PUT_FAST = 'PUT_FAST';
    const JUMP_BACK = 'JUMP_BACK';
    const MAKE_FUNCTION = 'MAKE_FUNCTION';
    const RETURN_VALUE = 'RETURN_VALUE';
    const TYPEOF = 'TYPEOF';
    const STORE_ARRAY = 'STORE_ARRAY';
    const LOAD_MEMBER = 'LOAD_MEMBER';
    const STORE_MEMBER = 'STORE_MEMBER';
    const ARRAY_PUSH = 'ARRAY_PUSH';

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