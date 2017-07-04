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
    protected $target;

    /**
     * @var ExpressionNode
     */
    protected $value;

    /**
     * AssignExpression constructor.
     *
     * @param ExpressionNode $target
     * @param ExpressionNode $value
     */
    public function __construct(ExpressionNode $target, ExpressionNode $value)
    {
        $this->target = $target;
        $this->value  = $value;
    }

    /**
     * @return ExpressionNode
     */
    public function getTarget(): ExpressionNode
    {
        return $this->target;
    }

    /**
     * @return ExpressionNode
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->toString([$this->value]);
    }
}