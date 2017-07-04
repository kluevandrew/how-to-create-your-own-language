<?php


namespace Interpreter;


class ArrayValue extends XValue
{
    public function getValue()
    {
        return array_map(function (XValue $value) {
            return $value->getValue();
        }, parent::getValue());
    }

    public function get(XValue $index): XValue
    {
        return $this->value[$index->getValue()] ?? new XValue(null);
    }

    public function set(XValue $index, XValue $value)
    {
        $this->value[$index->getValue()] = $value;
    }

    public function push(XValue $value)
    {
        $this->value[] = $value;
    }

}