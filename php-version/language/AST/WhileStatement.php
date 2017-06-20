<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 21.06.17
 * Time: 1:29
 */

namespace AST;

class WhileStatement extends StatementNode
{
    /**
     * @var ExpressionNode
     */
    protected $condition;
    /**
     * @var array
     */
    protected $statements;

    /**
     * WhileStatement constructor.
     * @param \AST\ExpressionNode $condition
     * @param array $statements
     */
    public function __construct(ExpressionNode $condition, array $statements)
    {
        $this->condition = $condition;
        $this->statements = $statements;
    }

    /**
     * @return ExpressionNode
     */
    public function getCondition(): ExpressionNode
    {
        return $this->condition;
    }

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString([
            $this->condition,
            $this->statements,
        ]);
    }


}