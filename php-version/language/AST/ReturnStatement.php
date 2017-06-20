<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 19.06.17
 * Time: 0:31
 */

namespace AST;

class ReturnStatement extends StatementNode
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