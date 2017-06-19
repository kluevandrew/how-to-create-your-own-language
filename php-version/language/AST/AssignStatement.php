<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 23:45
 */

namespace AST;


/**
 * Class AST\AssignStatement
 */
class AssignStatement extends StatementNode
{
    /**
     * @var IdentifierExpression
     */
    protected $name;

    /**
     * @var ExpressionNode
     */
    protected $value;

    /**
     * AssignStatement constructor.
     * @param $name
     * @param $value
     */
    public function __construct(IdentifierExpression $name, ExpressionNode $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return IdentifierExpression
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ExpressionNode
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString([$this->name, $this->value]);
    }


}