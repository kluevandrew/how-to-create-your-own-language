<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 22:32
 */

namespace AST;


/**
 * Class AST\BinaryExpression
 */
class BinaryExpression extends ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    protected $left;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var ExpressionNode
     */
    protected $right;

    /**
     * BinaryExpression constructor.
     *
     * @param ExpressionNode $left
     * @param string         $operation
     * @param ExpressionNode $right
     */
    public function __construct(ExpressionNode $left, string $operation, ExpressionNode $right)
    {
        $this->left = $left;
        $this->operation = $operation;
        $this->right = $right;
    }

    /**
     * @return ExpressionNode
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return ExpressionNode
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString([$this->left, $this->operation, $this->right]);
    }


}