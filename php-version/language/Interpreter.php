<?php

class Interpreter
{
    /**
     * @var FunctionCode
     */
    protected $function;

    /**
     * @var int
     */
    protected $cursor = 0;

    protected static $evaluators = [
        'LOAD_CONST'         => 'evalLoadConst',
        'STORE_FAST'         => 'evalStoreFast',
        'LOAD_FAST'          => 'evalLoadFast',
        'BINARY_ADD'         => 'evalBinaryAdd',
        'BINARY_MINUS'       => 'evalBinaryMinus',
        'BINARY_MULTIPLY'    => 'evalBinaryMultiply',
        'BINARY_DIVIDE'      => 'evalBinaryDivide',
        'BINARY_INSTANCE_OF' => 'evalBinaryInstanceOf',
        'BINARY_POW'         => 'evalBinaryPow',
        'CALL_FUNCTION'      => 'evalCallFunction',
        'LOAD_GLOBAL'        => 'evalLoadGlobal',
    ];
    /**
     * @var \Interpreter\Stack
     */
    protected $stack;

    /**
     * @var \Interpreter\Scope
     */
    protected $scope;

    protected $std = [];

    /**
     * Interpreter constructor.
     */
    public function __construct()
    {
        $this->std = [
            'print' => function (...$args) {
                printf(...$args);
            },
        ];
    }

    public function run(FunctionCode $function): int
    {
        $this->reset();
        $this->function = $function;
        $this->stack    = new \Interpreter\Stack();
        $this->scope    = new \Interpreter\Scope();
        foreach ($this->std as $key => $value) {
            $this->scope->set($key, $value);
        }

        while ($opcode = $this->function->getOpcodeByIndex($this->cursor)) {

            $evaluator = self::$evaluators[$opcode->getType()] ?? null;

            if (!$evaluator) {
                throw new Error(
                    "Unexpected opcode {$opcode->getType()}"
                );
            }

            $this->{$evaluator}($opcode);
        }

        return 0;
    }

    protected function reset()
    {
        $this->cursor = 0;
    }

    protected function evalLoadConst(Opcode $opcode)
    {
        $value = $this->function->getConstantByIndex($opcode->getValue());

        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalStoreFast(Opcode $opcode)
    {
        $name = $this->function->getLocalByIndex($opcode->getValue());

        if ($this->scope->has($name)) {
            throw new Error("Variable {$name} is already declared");
        }

        $value = $this->stack->pop();
        $this->scope->set($name, $value);
        $this->cursor++;
    }

    protected function evalLoadFast(Opcode $opcode)
    {
        $name = $this->function->getLocalByIndex($opcode->getValue());

        if (!$this->scope->has($name)) {
            throw new Error("Unknown variable {$name}");
        }

        $this->stack->push(
            $this->scope->get($name)
        );

        $this->cursor++;
    }

    protected function evalBinaryAdd()
    {
        $right = $this->stack->pop();
        $left  = $this->stack->pop();

        $value = $left + $right;
        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalBinaryMinus()
    {
        $right = $this->stack->pop();
        $left  = $this->stack->pop();

        $value = $left - $right;
        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalBinaryMultiply()
    {
        $right = $this->stack->pop();
        $left  = $this->stack->pop();

        $value = $left * $right;
        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalBinaryDivide()
    {
        $right = $this->stack->pop();
        $left  = $this->stack->pop();

        $value = $left / $right;
        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalBinaryPow()
    {
        $right = $this->stack->pop();
        $left  = $this->stack->pop();

        $value = pow($left, $right);
        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalBinaryInstanceOf()
    {
        $right = $this->stack->pop();
        $left  = $this->stack->pop();

        $c1    = $left;
        $c2    = $right;
        $value = $c1 instanceof $c2;
        $this->stack->push($value);
        $this->cursor++;
    }

    protected function evalCallFunction(Opcode $opcode)
    {
        $args = [];
        for ($i = $opcode->getValue() - 1; $i >= 0; $i--) {
            $args[$i] = $this->stack->pop();
        }

        $fn     = $this->stack->pop();
        $result = $fn(...$args);

        $this->stack->push($result);
        $this->cursor++;
    }

    protected function evalLoadGlobal(Opcode $opcode)
    {
        $name = $this->function->getNameByIndex($opcode->getValue());

        if (!$this->scope->has($name)) {
            throw new Error("Unknown variable {$name}");
        }

        $value = $this->scope->get($name);

        $this->stack->push($value);
        $this->cursor++;
    }

}