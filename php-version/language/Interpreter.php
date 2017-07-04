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
        Opcode::RETURN_VALUE => 'noop',
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
        Opcode::TYPEOF => 'evalTypeof',
        Opcode::LOAD_MEMBER => 'evalLoadMember',
        Opcode::STORE_ARRAY => 'evalStoreArray',
        Opcode::STORE_MEMBER => 'evalStoreMember',
        Opcode::ARRAY_PUSH => 'evalArrayPush',
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
     *
     * @param \Interpreter\Stack|null $stack
     * @param \Interpreter\Scope|null $scope
     */
    public function __construct(\Interpreter\Stack $stack = null, \Interpreter\Scope $scope = null)
    {
        $this->stack = $stack ?? new \Interpreter\Stack();
        $this->scope = $scope ?? new \Interpreter\Scope(new \Interpreter\StdLib());
    }

    public function run(FunctionCode $function, $debug = false)
    {
        $this->reset();
        $this->function = $function;
        $this->debug = $debug;

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

        return $last instanceof \Interpreter\XValue ? $last->getValue() : 0;
    }

    protected function reset()
    {
        $this->cursor = 0;
    }

    protected function evalLoadConst(Opcode $opcode)
    {
        $value = $this->function->getConstantByIndex($opcode->getValue());

        $this->stack->push(new \Interpreter\XValue($value));
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

        $this->stack->push($this->scope->get($name));

        $this->cursor++;
    }

    protected function evalBinaryAdd()
    {
        $this->math(function ($l, $r) {
            if (is_string($l) || is_string($r)) {
                return $l . $r;
            }
            return $l + $r;
        }, __METHOD__);
    }

    protected function evalBinaryMinus()
    {
        $this->math(function ($l, $r) {
            return $l - $r;
        }, __METHOD__);
    }

    protected function evalBinaryMultiply()
    {
        $this->math(function ($l, $r) {
            return $l * $r;
        }, __METHOD__);
    }

    protected function evalBinaryDivide()
    {
        $this->math(function ($l, $r) {
            return $l / $r;
        }, __METHOD__);
    }

    protected function evalBinaryPow()
    {
        $this->math(function ($l, $r) {
            return pow($l, $r);
        }, __METHOD__);
    }

    protected function evalCompareGt()
    {
        $this->math(function ($l, $r) {
            return $l > $r;
        }, __METHOD__);
    }

    protected function evalCompareGte()
    {
        $this->math(function ($l, $r) {
            return $l >= $r;
        }, __METHOD__);
    }

    protected function evalCompareLt()
    {
        $this->math(function ($l, $r) {
            return $l < $r;
        }, __METHOD__);
    }


    protected function evalCompareLte()
    {
        $this->math(function ($l, $r) {
            return $l <= $r;
        }, __METHOD__);
    }

    protected function evalBooleanAnd()
    {
        $this->math(function ($l, $r) {
            return $l && $r;
        }, __METHOD__);
    }

    protected function evalBooleanOr()
    {
        $this->math(function ($l, $r) {
            return $l || $r;
        }, __METHOD__);
    }

    protected function math(callable $callback, $method)
    {
        $right = $this->stack->pop()->getValue();
        $left = $this->stack->pop()->getValue();

        $value = $callback($left, $right);

        $this->stack->push(new \Interpreter\XValue($value));
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
        } elseif ($fn instanceof \Interpreter\NativeFunction) {
            $result = $this->evalNativeFunctionCall($fn, $args);
        } else {
            throw new \RuntimeException('Call on not a function');
        }

        $this->cursor++;
        $this->stack->push(new \Interpreter\XValue($result));
    }

    protected function evalUserFunctionCall(\Interpreter\UserFunction $function, array $arguments)
    {
        $interpreter = new self($this->stack, new \Interpreter\Scope($this->scope));
        if (count($arguments) !== $function->getArgumentsCount()  && $function->getArgumentsCount() !== -1) {
            echo "Notice: bad function call " . $function->getName() . PHP_EOL;
        }

        foreach ($function->getCode()->getArguments() as $i => $argumentName) {
            $interpreter->scope->set($argumentName, $arguments[$i] ?? null);
        }

        return $interpreter->run($function->getCode(), true);
    }


    protected function evalNativeFunctionCall(\Interpreter\NativeFunction $function, array $arguments)
    {
        if (count($arguments) !== $function->getArgumentsCount() && $function->getArgumentsCount() !== -1) {
            echo "Notice: bad function call " . $function->getName() . PHP_EOL;
        }

        return $function->execute($arguments);
    }

    protected function evalLoadGlobal(Opcode $opcode)
    {
        $name = $this->function->getNameByIndex($opcode->getValue());

        if (!$this->scope->has($name)) {
            throw new Error("Unknown variable {$name}");
        }

        $value = $this->scope->get($name);
        $this->stack->push($value instanceof \Interpreter\XValue ? $value : new \Interpreter\XValue($value));
        $this->cursor++;
    }

    protected function evalJumpIfFalse(Opcode $opcode)
    {
        $value = $this->stack->pop()->getValue();
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

    protected function evalTypeof()
    {
        $value = $this->stack->pop();
        $this->stack->push(new \Interpreter\XValue($value->getType()));
        $this->cursor++;
    }

    public function noop() {
        $this->cursor++;
    }

    public function evalLoadMember()
    {
        $owner = $this->stack->pop();
        $member = $this->stack->pop();

        if (!$owner instanceof \Interpreter\ArrayValue) {
            throw new \RuntimeException();
        }

        $this->stack->push($owner->get($member));
        $this->cursor++;
    }

    public function evalStoreArray(Opcode $opcode)
    {
        $items = [];
        for ($i = 0; $i < $opcode->getValue(); $i++) {
            $items[] = $this->stack->pop();
        }

        $this->stack->push(new \Interpreter\ArrayValue($items));
        $this->cursor++;
    }

    public function evalStoreMember()
    {
        $member = $this->stack->pop();
        $owner = $this->stack->pop();
        $value = $this->stack->pop();
        if (!$owner instanceof \Interpreter\ArrayValue) {
            throw new \RuntimeException();
        }
        $owner->set($member, $value);
        $this->cursor++;
    }

    public function evalArrayPush()
    {
        $owner = $this->stack->pop();
        $value = $this->stack->pop();
        if (!$owner instanceof \Interpreter\ArrayValue) {
            throw new \RuntimeException();
        }
        $owner->push($value);
        $this->cursor++;
    }

}