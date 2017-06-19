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
        $this->root   = new RootNode();
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
                    "Unexpected token %s, expected one of [%s] at line %d at pos %d`",
                    $this->current()->getType(),
                    implode(', ', (array)$types),
                    $this->current()->getLine(),
                    $this->current()->getPosition()
                )
            );
        }
    }


    /**
     * @param int $i
     *
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
        return new \AST\ExpressionStatement($this->parseExpression());
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

        $text  = $this->current()->getValue();
        $value = (int)($text);
        $this->next();

        return new \AST\LiteralExpression($text, $value);
    }


    protected function parseExpression()
    {
        $unary = $this->parseUnaryExpression();

        return $this->parseBinaryExpression($unary, -1);
    }

    protected function parseUnaryExpression()
    {
        $result = $this->parseAtomicExpression();

        while (true) {
            if ($this->current()->is(Token::TYPE_OPEN_PARENTHESIS)) {
                $this->next();
                $callee    = $result;
                $arguments = $this->parseCallExpressionArguments();
                $result    = new AST\CallExpression($callee, $arguments);
                $this->assertToken(Token::TYPE_CLOSE_PARENTHESIS);
                $this->next();
            } else {
                break;
            }
        }

        return $result;
    }

    protected function parseAtomicExpression()
    {
        $this->assertToken([
            Token::TYPE_IDENTIFIER,
            Token::TYPE_NUMBER,
            Token::TYPE_OPEN_PARENTHESIS,
        ]);

        if ($this->current()->is(Token::TYPE_IDENTIFIER)) {
            return $this->parseIdentifier();
        } else {
            if ($this->current()->is(Token::TYPE_OPEN_PARENTHESIS)) {
                return $this->parseParentheses();
            }
        }

        return $this->parseLiteral();
    }

    protected function parseParentheses()
    {
        $this->assertToken(Token::TYPE_OPEN_PARENTHESIS);
        $this->next();
        $expression = $this->parseExpression();
        $this->assertToken(Token::TYPE_CLOSE_PARENTHESIS);
        $this->next();

        return new \AST\ParenthesesExpression($expression);
    }

    protected function parseBinaryExpression($left, $minPrecedence)
    {
        $precedence = $this->current()->getPrecedence();

        if ($precedence !== null && $precedence > $minPrecedence) {
            $operation = $this->current()->getValue();
            $this->next();
            $right = $this->parseBinaryExpression($u = $this->parseUnaryExpression(), $precedence);

            return $this->parseBinaryExpression(
                new \AST\BinaryExpression($left, $operation, $right),
                $minPrecedence
            );
        }

        return $left;
    }


    protected function parseCallExpression($callee)
    {
        $arguments = [];

        $this->assertToken(Token::TYPE_OPEN_PARENTHESIS);
        $this->next();

        while (true) {
            $arguments[] = $this->parseExpression();
            if ($this->current()->is(Token::TYPE_COMMA)) {
                $this->next();
            } else {
                break;
            }
        }

        $this->assertToken(Token::TYPE_CLOSE_PARENTHESIS);
        $this->next();

        return new \AST\CallExpression($callee, $arguments);
    }

    protected function parseCallExpressionArguments()
    {
        $arguments = [];

        while (!$this->current()->is(Token::TYPE_CLOSE_PARENTHESIS)) {
            $arguments[] = $this->parseExpression();
            $this->assertToken([Token::TYPE_COMMA, Token::TYPE_CLOSE_PARENTHESIS]);
            if ($this->current()->is(Token::TYPE_COMMA)) {
                $this->next();
            }
        }

        return $arguments;
    }

}