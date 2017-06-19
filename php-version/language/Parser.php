<?php
use AST\RootNode;

class Parser
{
    protected $cursor = 0;
    /**
     * @var RootNode
     */
    protected $root;

    /**
     * @var Token
     */
    protected $tokens = [];

    public function parse(array $tokens): RootNode
    {
        $this->reset($tokens);
        $this->parseRoot();

        return $this->root;
    }

    protected function reset(array $tokens)
    {
        $this->root = new RootNode();
        $this->tokens = $tokens;
        $this->cursor = 0;
    }

    protected function next($step = 1)
    {
        $this->cursor += $step;

        return $this->current();
    }

    /**
     * @return Token|null
     */
    protected function current()
    {
        return $this->tokens[$this->cursor] ?? null;
    }

    /**
     * @param array|string $types
     */
    protected function assertToken($types)
    {
        if (false === $this->current()->is($types)) {
            throw new \RuntimeException(
                sprintf(
                    "Unexpected token %s, expected one of [%s]`",
                    $this->current()->getType(),
                    implode(', ', (array)$types)
                )
            );
        }
    }


    /**
     * @param int $i
     * @return Token
     */
    protected function lookahead($i = 1)
    {
        return $this->tokens[$this->cursor + $i] ?? null;
    }


    protected function parseRoot()
    {
        $statements = [];

        while ($this->current() && !$this->current()->is(Token::TYPE_EOF)) {
            $statements[] = $this->parseStatement();
        }


        $this->root->setStatements($statements);
    }

    protected function parseStatement()
    {
        if ($this->current()->is([Token::TYPE_LET, Token::TYPE_VAR])) {
            return $this->parseVariableDeclaration();
        }

        return $this->parseExpressionStatement();
    }

    protected function parseVariableDeclaration()
    {
        $this->assertToken([Token::TYPE_LET, Token::TYPE_VAR]);
        $this->next();

        $name = $this->parseIdentifier();

        if ($this->current() && $this->current()->is(Token::TYPE_EQUALS)) {
            $this->next();
            $initial = $this->parseExpression();
        } else {
            $initial = new \AST\LiteralExpression(0, 0);
        }


        return new \AST\AssignStatement($name, $initial);
    }

    protected function parseExpressionStatement()
    {
        $expr = $this->parseExpression();

        $this->next();

        return new \AST\ExpressionStatement($expr);
    }

    protected function parseIdentifier()
    {
        $this->assertToken(Token::TYPE_IDENTIFIER);

        $value = $this->current()->getValue();
        $this->next();

        return new \AST\IdentifierExpression($value);
    }

    protected function parseLiteral()
    {
        $this->assertToken(Token::TYPE_NUMBER);

        $text = $this->current()->getValue();
        $value = (int)($text);
        $this->next();

        return new \AST\LiteralExpression($text, $value);
    }

    protected function parseExpression()
    {
        if ($this->current()->is(Token::TYPE_NUMBER)) {

            $expression = $this->parseLiteral();
        } else {
            $expression = $this->parseIdentifier();

        }

        if ($this->current()) {
            switch ($this->current()->getType()) {
                case Token::TYPE_PLUS:
                case Token::TYPE_MINUS:
                case Token::TYPE_MULTIPLY:
                case Token::TYPE_DIVISION:
                    return $this->parseBinaryExpression($expression);
                case Token::TYPE_OPEN_PAREN:
                    return $this->parseCallExpression($expression);
            }
        }

        return $expression;
    }

    protected function parseBinaryExpression($left)
    {
        $this->assertToken([
            Token::TYPE_PLUS,
            Token::TYPE_MINUS,
            Token::TYPE_MULTIPLY,
            Token::TYPE_DIVISION,
        ]);

        $operation = $this->current()->getValue();

        $this->next();

        $right = $this->parseExpression();

        return new \AST\BinaryExpression($left, $operation, $right);
    }

    protected function parseCallExpression($callee)
    {
        $arguments = [];

        $this->assertToken(Token::TYPE_OPEN_PAREN);
        $this->next();

        while (true) {
            $arguments[] = $this->parseExpression();
            if ($this->current()->is(Token::TYPE_COMMA)) {
                $this->next();
            } else {
                break;
            }
        }

        $this->assertToken(Token::TYPE_CLOSE_PAREN);
        $this->next();

        return new \AST\CallExpression($callee, $arguments);
    }


}