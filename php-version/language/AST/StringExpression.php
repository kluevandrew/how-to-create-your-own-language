<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 20.06.17
 * Time: 19:29
 */

namespace AST;


class StringExpression extends ExpressionNode
{
    protected $value;
    protected $quote;

    public function __construct($value, $quote)
    {
        $this->quote = $quote;
        $this->value = $value;
    }

    public function getQuote()
    {
        return $this->quote;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->toString([]) . ' ' . $this->value;
    }

}