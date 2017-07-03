<?php


namespace AST;


class TypeofExpression extends ExpressionNode
{
    protected $name;
    protected $expression;

    /**
     * IdentifierExpression constructor.
     *
     * @param $name
     * @param $expression
     */
    public function __construct($name, ExpressionNode $expression)
    {
        $this->name       = $name;
        $this->expression = $expression;
    }

    public function __toString()
    {
        return $this->toString([]) . ' ' . $this->expression;
    }

    /**
     * @return ExpressionNode
     */
    public function getExpression(): ExpressionNode
    {
        return $this->expression;
    }

}