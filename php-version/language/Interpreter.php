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
        Opcode::MAKE_FUNCTION => 'evalMakeFunction',
        Opcode::RETURN_VALUE => 'noop',
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
    protected $debug;

    /**
     * Interpreter constructor.
     */
    public function __construct()
    {
        $this->stack = new \Interpreter\Stack();
        $this->scope = new \Interpreter\Scope();

        $this->std = [
            'print' => 'printf',
            'random' => 'random_int'
        ];
    }

    public function run(FunctionCode $function, $debug = false): int
    {
        $this->reset();
        $this->function = $function;
        $this->debug = $debug;

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

        $last = $this->stack->pop();
        return is_int($last) ? $last : 0;
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
        for ($i = 0; $i < $opcode->getValue(); $i++) {
            $args[$i] = $this->stack->pop();
        }
        $args = array_reverse($args);

        $fn = $this->stack->pop();

        if ($fn instanceof \Interpreter\UserFunction) {
            $result = $this->evalUserFunctionCall($fn, $args);
        } else {
            $result = $fn(...$args);
        }

        $this->cursor++;
        $this->stack->push($result);
    }

    protected function evalUserFunctionCall(\Interpreter\UserFunction $function, array $arguments)
    {
        $interpreter = new self();
        $interpreter->stack = $this->stack;
        foreach ($arguments as $i => $argument) {
            $interpreter->scope->set($function->getCode()->getArgumentByIndex($i), $argument);
        }

        $value = $interpreter->run($function->getCode(), true);

        return $value;
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

    protected function evalMakeFunction()
    {
        $name = $this->stack->pop();
        $function = $this->stack->pop();
        $this->stack->push(new \Interpreter\UserFunction($name, $function));
        $this->cursor++;
    }

    public function noop() {
        $this->cursor++;
    }
}