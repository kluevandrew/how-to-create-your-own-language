<?php

namespace AST;

class ForStatement extends StatementNode
{
    /**
     * @var AssignStatement
     */
    protected $initial;

    /**
     * @var ExpressionStatement
     */
    protected $step;

    /**
     * @var ExpressionStatement
     */
    protected $condition;

    /**
     * @var array
     */
    protected $statements;

    /**
     * ForStatement constructor.
     * @param Node $initial
     * @param ExpressionStatement $step
     * @param ExpressionStatement $condition
     * @param array $statements
     */
    public function __construct(Node $initial, ExpressionStatement $step, ExpressionStatement $condition, array $statements)
    {
        $this->initial = $initial;
        $this->step = $step;
        $this->condition = $condition;
        $this->statements = $statements;
    }

    /**
     * @return AssignStatement
     */
    public function getInitial(): AssignStatement
    {
        return $this->initial;
    }

    /**
     * @return ExpressionStatement
     */
    public function getStep(): ExpressionStatement
    {
        return $this->step;
    }

    /**
     * @return ExpressionStatement
     */
    public function getCondition(): ExpressionStatement
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

    public function __toString()
    {
        return $this->toString([
            $this->initial,
            $this->step,
            $this->condition,
            $this->statements,
        ]);
    }


}