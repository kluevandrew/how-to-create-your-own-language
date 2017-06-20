<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 21.06.17
 * Time: 0:14
 */

namespace AST;

class AssignExpression extends ExpressionNode
{
    /**
     * @var IdentifierExpression
     */
    protected $name;

    /**
     * @var ExpressionNode
     */
    protected $expression;

    /**
     * AssignExpression constructor.
     * @param IdentifierExpression $name
     * @param ExpressionNode $expression
     */
    public function __construct(IdentifierExpression $name, ExpressionNode $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }

    /**
     * @return IdentifierExpression
     */
    public function getName(): IdentifierExpression
    {
        return $this->name;
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