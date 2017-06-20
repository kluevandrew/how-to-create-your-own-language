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
        Opcode::LOAD_CONST => 'evalLoadConst',
        Opcode::STORE_FAST => 'evalStoreFast',
        Opcode::PUT_FAST => 'evalPutFast',
        Opcode::LOAD_FAST => 'evalLoadFast',
        Opcode::BINARY_ADD => 'evalBinaryAdd',
        Opcode::BINARY_MINUS => 'evalBinaryMinus',
        Opcode::BINARY_MULTIPLY => 'evalBinaryMultiply',
        Opcode::BINARY_DIVIDE => 'evalBinaryDivide',
        Opcode::BINARY_INSTANCE_OF => 'evalBinaryInstanceOf',
        Opcode::BINARY_POW => 'evalBinaryPow',
        Opcode::CALL_FUNCTION => 'evalCallFunction',
        Opcode::LOAD_GLOBAL => 'evalLoadGlobal',
        Opcode::COMPARE_GT => 'evalCompareGt',
        Opcode::COMPARE_GTE => 'evalCompareGte',
        Opcode::COMPARE_LT => 'evalCompareLt',
        Opcode::COMPARE_LTE => 'evalCompareLte',
        Opcode::JUMP_IF_FALSE => 'evalJumpIfFalse',
        Opcode::JUMP => 'evalJump',
        Opcode::JUMP_BACK => 'evalJumpBack',
        Opcode::BOOLEAN_AND => 'evalBooleanAnd',
        Opcode::BOOLEAN_OR => 'evalBooleanOr',
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
            'random' => function ($min, $max) {
                return random_int($min, $max);
            },
        ];
    }

    public function run(FunctionCode $function): int
    {
        $this->reset();
        $this->function = $function;
        $this->stack = new \Interpreter\Stack();
        $this->scope = new \Interpreter\Scope();
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

    protected function evalPutFast(Opcode $opcode)
    {
        $name = $this->function->getLocalByIndex($opcode->getValue());

        if (!$this->scope->has($name)) {
            throw new Error("Variable {$name} is never declared");
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
        $this->math(function ($l, $r) {
            return $l + $r;
        });
    }

    protected function evalBinaryMinus()
    {
        $this->math(function ($l, $r) {
            return $l - $r;
        });
    }

    protected function evalBinaryMultiply()
    {
        $this->math(function ($l, $r) {
            return $l * $r;
        });
    }

    protected function evalBinaryDivide()
    {
        $this->math(function ($l, $r) {
            return $l / $r;
        });
    }

    protected function evalBinaryPow()
    {
        $this->math(function ($l, $r) {
            return pow($l, $r);
        });
    }

    protected function evalCompareGt()
    {
        $this->math(function ($l, $r) {
            return $l > $r;
        });
    }

    protected function evalCompareGte()
    {
        $this->math(function ($l, $r) {
            return $l >= $r;
        });
    }

    protected function evalCompareLt()
    {
        $this->math(function ($l, $r) {
            return $l < $r;
        });
    }


    protected function evalCompareLte()
    {
        $this->math(function ($l, $r) {
            return $l <= $r;
        });
    }

    protected function evalBooleanAnd()
    {
        $this->math(function ($l, $r) {
            return $l && $r;
        });
    }

    protected function evalBooleanOr()
    {
        $this->math(function ($l, $r) {
            return $l || $r;
        });
    }

    protected function math(callable $callback)
    {
        $right = $this->stack->pop();
        $left = $this->stack->pop();

        $value = $callback($left, $right);

        $this->stack->push($value);
        $this->cursor++;
    }


    protected function evalCallFunction(Opcode $opcode)
    {
        $args = [];
        for ($i = $opcode->getValue() - 1; $i >= 0; $i--) {
            $args[$i] = $this->stack->pop();
        }

        $fn = $this->stack->pop();
        $result = $fn(...array_reverse($args));

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

    protected function evalJumpIfFalse(Opcode $opcode)
    {
        $value = $this->stack->pop();
        $this->cursor++;

        if ($value == false) {
            $this->cursor += $opcode->getValue();
        }

    }

    protected function evalJump(Opcode $opcode)
    {
        $this->cursor++;
        $this->cursor += $opcode->getValue();
    }

    protected function evalJumpBack(Opcode $opcode)
    {
        $this->cursor -= $opcode->getValue();
    }

}