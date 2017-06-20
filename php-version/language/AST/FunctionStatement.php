<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 21.06.17
 * Time: 1:47
 */

namespace AST;


class FunctionStatement extends StatementNode
{
    /**
     * @var IdentifierExpression
     */
    protected $name;
    /**
     * @var array|IdentifierExpression[]
     */
    protected $arguments;
    /**
     * @var array|StatementNode[]
     */
    protected $statements;

    /**
     * FunctionStatement constructor.
     * @param \AST\IdentifierExpression $name
     * @param IdentifierExpression[] $arguments
     * @param StatementNode[] $statements
     */
    public function __construct($name, array $arguments, array $statements)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->statements = $statements;
    }

    /**
     * @return IdentifierExpression
     */
    public function getName(): IdentifierExpression
    {
        return $this->name;
    }

    /**
     * @return array|IdentifierExpression[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return array|StatementNode[]
     */
    public function getStatements()
    {
        return $this->statements;
    }

    public function __toString()
    {
        return $this->toString([
            $this->name,
            $this->arguments,
            $this->statements,
        ]);
    }


}