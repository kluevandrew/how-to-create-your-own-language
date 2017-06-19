<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 22:32
 */

namespace AST;


class BinaryExpression extends ExpressionNode
{
    protected $left;

    protected $operation;

    protected $right;

    /**
     * BinaryExpression constructor.
     * @param $left
     * @param $operation
     * @param $right
     */
    public function __construct($left, $operation, $right)
    {
        $this->left = $left;
        $this->operation = $operation;
        $this->right = $right;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return mixed
     */
    public function getRight()
    {
        return $this->right;
    }

    public function __toString()
    {
        return $this->toString([$this->left, $this->operation, $this->right]);
    }


}