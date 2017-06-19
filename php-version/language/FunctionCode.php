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
     * @return \Interpreter\XValue|null
     */
    public function getLocalByIndex($index)
    {
        return $this->locals[$index];
    }

    public function getConstantByIndex($index)
    {
        return $this->constants[$index];
    }

    public function getNameByIndex($index)
    {
        return $this->names[$index];
    }
}