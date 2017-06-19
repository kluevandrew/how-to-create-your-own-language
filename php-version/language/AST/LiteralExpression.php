<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 23:47
 */

namespace AST;


class LiteralExpression extends ExpressionNode
{
    protected $name;

    protected $value;

    /**
     * IdentifierExpression constructor.
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
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