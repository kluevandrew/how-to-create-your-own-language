<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 23:47
 */

namespace AST;


class IdentifierExpression extends ExpressionNode
{
    protected $name;

    /**
     * IdentifierExpression constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->toString([$this->name]);
    }
}