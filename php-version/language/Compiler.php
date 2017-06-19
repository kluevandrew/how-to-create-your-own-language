<?php

use AST\RootNode;

class Compiler
{
    /**
     * @var RootNode
     */
    protected $ast;

    /**
     * @var FunctionCode
     */
    protected $function;

    private static $compilers = [
        \AST\AssignStatement::class       => 'compileAssignStatement',
        \AST\ExpressionStatement::class   => 'compileExpressionStatement',
        \AST\BinaryExpression::class      => 'compileBinaryExpression',
        \AST\LiteralExpression::class     => 'compileLiteralExpression',
        \AST\IdentifierExpression::class  => 'compileIdentifierExpression',
        \AST\CallExpression::class        => 'compileCallExpression',
        \AST\ParenthesesExpression::class => 'compileParenthesesExpression',
    ];

    public function compile(RootNode $ast): FunctionCode
    {
        $this->ast      = $ast;
        $this->function = new FunctionCode('#main#', []);

        $this->compileProgram();

        return $this->function;
    }

    protected function compileProgram()
    {
        foreach ($this->ast->getStatements() as $statement) {
            $this->compileStatement($statement);
        }
    }

    protected function compileNode(\AST\Node $node)
    {
        $compiler = self::$compilers[get_class($node)] ?? null;
        if (!$compiler) {
            throw new \RuntimeException();
        }

        $this->{$compiler}($node);
    }

    protected function compileStatement(\AST\StatementNode $statement)
    {
        $this->compileNode($statement);
    }

    protected function compileAssignStatement(\AST\AssignStatement $statement)
    {
        $this->compileExpression($statement->getValue());

        $this->function->opcode(
            new Opcode(
                Opcode::STORE_FAST,
                $this->function->local($statement->getName()->getName())
            )
        );
    }

    protected function compileExpressionStatement(\AST\ExpressionStatement $statement)
    {
        $this->compileExpression($statement->getExpression());
    }

    protected function compileExpression(\AST\ExpressionNode $expression)
    {
        $this->compileNode($expression);
    }

    protected function compileBinaryExpression(\AST\BinaryExpression $expression)
    {
        $this->compileExpression($expression->getLeft());
        $this->compileExpression($expression->getRight());

        $operations = [
            '+' => Opcode::BINARY_ADD,
            '-' => Opcode::BINARY_MINUS,
            '*' => Opcode::BINARY_MULTIPLY,
            '/' => Opcode::BINARY_DIVIDE,
            'instanceof' => Opcode::BINARY_INSTANCE_OF,
            '^' => Opcode::BINARY_POW,
        ];

        $this->function->opcode(new Opcode(
            $operations[$expression->getOperation()],
            0
        ));
    }

    protected function compileLiteralExpression(\AST\LiteralExpression $expression)
    {
        $this->function->opcode(new Opcode(
            Opcode::LOAD_CONST,
            $this->function->constant(
                $expression->getValue()
            )
        ));
    }

    protected function compileIdentifierExpression(\AST\IdentifierExpression $expression)
    {
        if ($this->function->hasLocal($expression->getName())) {
            $this->function->opcode(
                new Opcode(
                    Opcode::LOAD_FAST,
                    $this->function->local($expression->getName())
                )
            );
            return;
        }

        $this->function->opcode(
            new Opcode(
                Opcode::LOAD_GLOBAL,
                $this->function->name($expression->getName())
            )
        );

    }

    protected function compileCallExpression(\AST\CallExpression $expression)
    {
        $callee = $expression->getCallee();
        $arguments = $expression->getArguments();

        $this->compileExpression($callee);
        foreach ($arguments as $argument) {
            $this->compileExpression($argument);
        }

        $this->function->opcode(
            new Opcode(
                Opcode::CALL_FUNCTION,
                count($arguments)
            )
        );
    }

    protected function compileParenthesesExpression(\AST\ParenthesesExpression $expression)
    {
        $this->compileExpression($expression->getExpression());
    }

}



