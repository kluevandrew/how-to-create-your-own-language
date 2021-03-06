<?php


class FunctionCode
{

    protected $name;
    protected $arguments;

    protected $opcodes = [];
    protected $locals = [];
    protected $constants = [];
    protected $names = [];

    public function __construct($name, $arguments)
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function opcode(Opcode $opcode)
    {
        $this->opcodes[] = $opcode;
    }

    public function local($value)
    {
        $index = array_search($value, $this->locals, true);
        if ($index > -1) {
            return $index;
        }

        $this->locals[] = $value;

        return count($this->locals) - 1;
    }

    public function constant($constant)
    {
        $index = array_search($constant, $this->constants, true);
        if ($index > -1) {
            return $index;
        }

        $this->constants[] = $constant;

        return count($this->constants) - 1;
    }


    public function name($name)
    {
        $index = array_search($name, $this->names, true);
        if ($index > -1) {
            return $index;
        }

        $this->names[] = $name;

        return count($this->names) - 1;
    }

    public function hasLocal($name)
    {
        return in_array($name, $this->locals, true);
    }

    /**
     * @return array
     */
    public function getOpcodes(): array
    {
        return $this->opcodes;
    }

    /**
     * @param $index
     *
     * @return Opcode|null
     */
    public function getOpcodeByIndex($index) {
        return $this->opcodes[$index] ?? null;

    }

    public function getOpcodesCount() : int {
        return count($this->opcodes);
    }

    /**
     * @param $index
     *
     * @return \Interpreter\null
     */
    public function getLocalByIndex($index)
    {
        return $this->locals[$index] ?? null;
    }

    public function getConstantByIndex($index)
    {
        return $this->constants[$index] ?? null;
    }

    public function getNameByIndex($index)
    {
        return $this->names[$index];
    }

    public function getArgumentByIndex($index)
    {
        return $this->arguments[$index];
    }

    public function getArgumentsCount()
    {
        return count($this->arguments);
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function fake($name) {

        $fake = new self($name, []);
        $fake->locals = &$this->locals;
        $fake->names = &$this->names;
        $fake->constants = &$this->constants;

        return $fake;
    }

    public function merge(self $function)
    {
        $this->opcodes = array_merge($this->opcodes, $function->opcodes);
    }

}