<?php

namespace AST;

class IfStatement extends StatementNode
{
    /**
     * @var array
     */
    protected $statements = [];
    /**
     * @var ParenthesesExpression|null
     */
    protected $expression;
    /**
     * @var IfStatement|null
     */
    protected $else;

    /**
     * IfStatement constructor.
     * @param array $statements
     * @param \AST\ParenthesesExpression|null $expression
     * @param \AST\IfStatement|null $else
     */
    public function __construct(array $statements, $expression = null, $else = null)
    {
        $this->statements = $statements;
        $this->expression = $expression;
        $this->else = $else;
    }

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @return ParenthesesExpression|null
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return IfStatement|null
     */
    public function getElse()
    {
        return $this->else;
    }

    public function __toString()
    {
        return $this->toString([
            $this->expression,
            $this->statements,
            $this->else
        ]);
    }

}