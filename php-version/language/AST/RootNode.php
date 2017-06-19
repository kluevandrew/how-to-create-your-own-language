<?php

namespace AST;

class RootNode extends Node
{
    /**
     * @var array
     */
    protected $statements = [];

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @param array $statements
     */
    public function setStatements(array $statements)
    {
        $this->statements = $statements;
    }

    public function __toString()
    {
        return implode(PHP_EOL, $this->statements);
    }
}