<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 22:33
 */

namespace AST;


class CallExpression extends ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    protected $callee;

    protected $arguments = [];

    /**
     * CallExpression constructor.
     * @param ExpressionNode $callee
     * @param array $arguments
     */
    public function __construct(ExpressionNode $callee, array $arguments)
    {
        $this->callee = $callee;
        $this->arguments = $arguments;
    }

    /**
     * @return ExpressionNode
     */
    public function getCallee()
    {
        return $this->callee;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function __toString()
    {
        return $this->toString([$this->callee, $this->arguments]);
    }
}