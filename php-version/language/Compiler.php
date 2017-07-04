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

    /**
     * @var string
     */
    protected $functionName = '#main#';

    private static $compilers = [
        \AST\AssignStatement::class => 'compileAssignStatement',
        \AST\IfStatement::class => 'compileIfStatement',
        \AST\ExpressionStatement::class => 'compileExpressionStatement',
        \AST\TypeofExpression::class => 'compileTypeofExpression',
        \AST\BinaryExpression::class => 'compileBinaryExpression',
        \AST\LiteralExpression::class => 'compileLiteralExpression',
        \AST\IdentifierExpression::class => 'compileIdentifierExpression',
        \AST\CallExpression::class => 'compileCallExpression',
        \AST\ParenthesesExpression::class => 'compileParenthesesExpression',
        \AST\StringExpression::class => 'compileStringExpression',
        \AST\ArrayExpression::class => 'compileArrayExpression',
        \AST\MemberExpression::class => 'compileMemberExpression',
        \AST\AssignExpression::class => 'compileAssignExpression',
        \AST\ForStatement::class => 'compileForStatement',
        \AST\WhileStatement::class => 'compileWhileStatement',
        \AST\FunctionStatement::class => 'compileFunctionStatement',
        \AST\ReturnStatement::class => 'compileReturnStatement',
    ];

    public function compile(RootNode $ast): FunctionCode
    {
        $this->ast = $ast;
        $this->function = new FunctionCode($this->functionName, []);

        $this->compileProgram($this->ast->getStatements());

        return $this->function;
    }


    protected function compileProgram($statements)
    {
        foreach ($statements as $statement) {
            $this->compileStatement($statement);
        }
    }

    /**
     * @param $nodes
     * @param $name
     * @return FunctionCode
     */
    protected function compileFakeFunction($nodes, $name)
    {
        $compiler = new self();
        $compiler->function = $this->function->fake($name);
        foreach ($nodes as $node) {
            $compiler->compileNode($node);
        }

        return $compiler->function;
    }

    /**
     * @param FunctionCode $function
     * @param $statements
     * @return FunctionCode
     */
    protected function compileBody(FunctionCode $function, $statements)
    {
        $compiler = new self();
        $compiler->function = $function;

        foreach ($statements as $statement) {
            $compiler->compileStatement($statement);
        }

        if ($function->getOpcodesCount() === 0 ||
            $function->getOpcodeByIndex($function->getOpcodesCount() - 1)->getType() !== Opcode::RETURN_VALUE
        ) {
            $function->opcode(new Opcode(Opcode::LOAD_CONST, $function->constant(null)));
            $function->opcode(new Opcode(Opcode::RETURN_VALUE));
        }

        return $function;
    }

    protected function compileNode(\AST\Node $node)
    {
        $compiler = self::$compilers[get_class($node)] ?? null;
        if (!$compiler) {
            throw new \RuntimeException(sprintf('Unexpected node ' . get_class($node)));
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

    protected function compileIfStatement(\AST\IfStatement $statement)
    {
        $fakeFunctionIf = $this->compileFakeFunction($statement->getStatements(), '#' . get_class($statement) . '#');
        $fakeFunctionElse = null;

        if ($statement->getElse()) {
            $fakeFunctionElse = $this->compileFakeFunction([$statement->getElse()], '#' . get_class($statement) . '#');
            $fakeFunctionIf->opcode(new Opcode(
                Opcode::JUMP,
                $fakeFunctionElse->getOpcodesCount()
            ));
        }

        if ($statement->getExpression()) {
            $this->compileExpression($statement->getExpression());
            $this->function->opcode(
                new Opcode(
                    Opcode::JUMP_IF_FALSE,
                    $fakeFunctionIf->getOpcodesCount()
                )
            );
        }

        $this->function->merge($fakeFunctionIf);
        if ($fakeFunctionElse) {
            $this->function->merge($fakeFunctionElse);
        }
    }

    protected function compileForStatement(\AST\ForStatement $statement)
    {
        $this->compileNode($statement->getInitial());
        $this->compileNode($statement->getCondition());

        $bodyStatements = $statement->getStatements();
        $bodyStatements[] = $statement->getIteration();
        $bodyStatements[] = $statement->getCondition();
        $body = $this->compileFakeFunction(
            $bodyStatements,
            '#' . get_class($statement) . '#'
        );
        $body->opcode(
            new Opcode(
                Opcode::JUMP_BACK,
                $body->getOpcodesCount() + 1
            )
        );

        $this->function->opcode(new Opcode(
            Opcode::JUMP_IF_FALSE,
            $body->getOpcodesCount()
        ));
        $this->function->merge($body);
    }

    protected function compileWhileStatement(\AST\WhileStatement $statement)
    {
        $this->compileNode($statement->getCondition());

        $bodyStatements = $statement->getStatements();
        $bodyStatements[] = $statement->getCondition();
        $body = $this->compileFakeFunction(
            $bodyStatements,
            '#' . get_class($statement) . '#'
        );
        $body->opcode(
            new Opcode(
                Opcode::JUMP_BACK,
                $body->getOpcodesCount() + 1
            )
        );

        $this->function->opcode(new Opcode(
            Opcode::JUMP_IF_FALSE,
            $body->getOpcodesCount()
        ));
        $this->function->merge($body);
    }

    protected function compileExpressionStatement(\AST\ExpressionStatement $statement)
    {
        $this->compileExpression($statement->getExpression());
    }

    protected function compileExpression(\AST\ExpressionNode $expression)
    {
        $this->compileNode($expression);
    }

    protected function compileTypeofExpression(\AST\TypeofExpression $expression)
    {
        $this->compileExpression($expression->getExpression());

        $this->function->opcode(new Opcode(
            Opcode::TYPEOF,
            0
        ));
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
            '^' => Opcode::BINARY_POW,
            '>' => Opcode::COMPARE_GT,
            '>=' => Opcode::COMPARE_GTE,
            '<' => Opcode::COMPARE_LT,
            '<=' => Opcode::COMPARE_LTE,
            '&&' => Opcode::BOOLEAN_AND,
            'and' => Opcode::BOOLEAN_AND,
            '||' => Opcode::BOOLEAN_OR,
            'or' => Opcode::BOOLEAN_OR,
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

    protected function compileStringExpression(\AST\StringExpression $expression)
    {
        $this->function->opcode(new Opcode(
            Opcode::LOAD_CONST,
            $this->function->constant(
                $expression->getValue()
            )
        ));
    }

    protected function compileArrayExpression(\AST\ArrayExpression $expression)
    {
        $items = array_reverse($expression->getItems());
        $keys = array_reverse($expression->getKeys());

        if (empty(array_filter($keys))) {
            foreach ($items as $index => $item) {
                $this->compileExpression($item);
            }

            $this->function->opcode(new Opcode(
                Opcode::STORE_ARRAY,
                count($items)
            ));
            return;
        }

        $this->function->opcode(new Opcode(
            Opcode::INIT_ARRAY
        ));

        foreach ($items as $index => $item) {
            if ($keys[$index]) {
                $this->compileExpression($keys[$index]);
                $this->compileExpression($item);
                $this->function->opcode(
                    new Opcode(
                        Opcode::STORE_MEMBER
                    )
                );
            } else {
                $this->compileExpression($item);
                $this->function->opcode(
                    new Opcode(
                        Opcode::ARRAY_PUSH
                    )
                );
            }
        }

    }

    protected function compileMemberExpression(\AST\MemberExpression $expression)
    {
        $this->compileExpression($expression->getMember());
        $this->compileExpression($expression->getOwner());

        $this->function->opcode(new Opcode(
            Opcode::LOAD_MEMBER
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

    protected function compileAssignExpression(\AST\AssignExpression $expression)
    {
        $target = $expression->getTarget();
        if ($target instanceof \AST\MemberExpression) {
            $this->compileExpression($target->getOwner());
            $this->compileExpression($target->getMember());
            $this->compileExpression($expression->getValue());
            $this->function->opcode(
                new Opcode(
                    Opcode::STORE_MEMBER
                )
            );
            return;
        }

        if ($target instanceof \AST\PushExpression) {
            $this->compileExpression($target->getOwner());
            $this->compileExpression($expression->getValue());

            $this->function->opcode(
                new Opcode(
                    Opcode::ARRAY_PUSH
                )
            );
            return;
        }

        if ($target instanceof \AST\IdentifierExpression) {
            $this->compileExpression($expression->getValue());
            $this->function->opcode(
                new Opcode(
                    Opcode::PUT_FAST,
                    $this->function->local($target->getName())
                )
            );
        }

        if ($target instanceof \AST\TypeofExpression) {
            $this->compileTypeofExpression($target);
            $this->function->opcode(
                new Opcode(
                    Opcode::PUT_FAST,
                    $this->function->local($target->getName())
                )
            );
        }
    }

    protected function compileFunctionStatement(\AST\FunctionStatement $statement)
    {
        $argumentsNames = [];
        foreach ($statement->getArguments() as $argument) {
            $argumentsNames[] = $argument->getName();
        }
        $function = new FunctionCode($statement->getName()->getName(), $argumentsNames);
        foreach ($statement->getArguments() as $argument) {
            $function->local($argument->getName());
        }

        $this->compileBody($function, $statement->getStatements());

        $this->function->opcode(new Opcode(
            Opcode::LOAD_CONST,
            $this->function->constant($function)
        ));
        $this->function->opcode(new Opcode(
            Opcode::LOAD_CONST,
            $this->function->constant($statement->getName()->getName())
        ));
        $this->function->opcode(new Opcode(
            Opcode::MAKE_FUNCTION,
            0
        ));
        $this->function->opcode(new Opcode(
            Opcode::STORE_FAST,
            $this->function->local($statement->getName()->getName())
        ));
    }

    protected function compileReturnStatement(\AST\ReturnStatement $statement)
    {
        $this->compileExpression($statement->getExpression());
        $this->function->opcode(new Opcode(
            Opcode::RETURN_VALUE,
            0
        ));
    }
}



