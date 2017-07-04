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
    protected $iteration;

    /**
     * @var ExpressionStatement
     */
    protected $condition;

    /**
     * ForStatement constructor.
     *
     * @param Node                $initial
     * @param ExpressionStatement $condition
     * @param ExpressionStatement $iteration
     * @param array               $statements
     */
    public function __construct(Node $initial, ExpressionStatement $condition, ExpressionStatement $iteration, array $statements)
    {
        $this->initial    = $initial;
        $this->iteration  = $iteration;
        $this->condition  = $condition;
        $this->statements = $statements;
    }

    /**
     * @var array
     */
    protected $statements;

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
    public function getIteration(): ExpressionStatement
    {
        return $this->iteration;
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
            $this->iteration,
            $this->condition,
            $this->statements,
        ]);
    }


}