<?php


namespace AST;


class ParenthesesExpression extends ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    protected $expression;

    /**
     * ExpressionStatement constructor.
     * @param ExpressionNode $expression
     */
    public function __construct(ExpressionNode $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return ExpressionNode
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