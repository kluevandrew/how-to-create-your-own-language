<?php


namespace AST;


class ParenthesesExpression extends ExpressionNode
{
    protected $expression;

    /**
     * ExpressionStatement constructor.
     * @param $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return mixed
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function __toString()
    {
        return $this->toString([$this->expression]);
    }
}