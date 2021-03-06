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
            return $this->parseAssignStatement();
        }

        if ($this->current()->is(Token::TYPE_IF)) {
            return $this->parseIfStatement();
        }

        if ($this->current()->is(Token::TYPE_FOR)) {
            return $this->parseForStatement();
        }

        if ($this->current()->is(Token::TYPE_WHILE)) {
            return $this->parseWhileStatement();
        }

        if ($this->current()->is(Token::TYPE_FUNCTION)) {
            return $this->parseFunctionStatement();
        }

        if ($this->current()->is(Token::TYPE_RETURN)) {
            return $this->parserReturnStatement();
        }

        return $this->parseExpressionStatement();
    }

    protected function parseAssignStatement()
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

    protected function parseIfStatement()
    {
        $this->assertToken([Token::TYPE_IF, Token::TYPE_ELSE_IF, Token::TYPE_ELSE]);

        $expression = null;
        if (!$this->current()->is(Token::TYPE_ELSE)) {
            $this->next();
            $expression = $this->parseParentheses();
        } else {
            $this->next();
        }

        $this->assertAndNext(Token::TYPE_CURLY_OPEN);

        $statements = [];
        while ($this->current() && !$this->current()->is(Token::TYPE_CURLY_CLOSE)) {
            $statements[] = $this->parseStatement();
        }

        $this->assertAndNext(Token::TYPE_CURLY_CLOSE);

        $else = null;
        if ($this->current()->is([Token::TYPE_ELSE, Token::TYPE_ELSE_IF])) {
            $else = $this->parseIfStatement();
        }

        return new \AST\IfStatement($statements, $expression, $else);
    }

    protected function parseForStatement()
    {
        $this->assertAndNext(Token::TYPE_FOR);
        $this->assertAndNext(Token::TYPE_OPEN_PARENTHESIS);

        $this->assertAndNext([Token::TYPE_LET, Token::TYPE_VAR]);
        $identifier = $this->parseIdentifier();
        $this->assertAndNext(Token::TYPE_EQUALS);
        $initial = new \AST\AssignStatement($identifier, $this->parseExpression());
        $this->assertAndNext(Token::TYPE_SEMICOLON);

        $condition = new \AST\ExpressionStatement($this->parseExpression());
        $this->assertAndNext(Token::TYPE_SEMICOLON);

        $iteration = new \AST\ExpressionStatement($this->parseExpression());
        $this->skipIf(Token::TYPE_SEMICOLON);

        $this->assertAndNext(Token::TYPE_CLOSE_PARENTHESIS);

        $this->assertAndNext(Token::TYPE_CURLY_OPEN);
        $statements = [];
        while ($this->current() && !$this->current()->is(Token::TYPE_CURLY_CLOSE)) {
            $statements[] = $this->parseStatement();
        }
        $this->assertAndNext(Token::TYPE_CURLY_CLOSE);

        return new \AST\ForStatement($initial, $condition, $iteration, $statements);
    }

    protected function parseWhileStatement()
    {
        $this->assertAndNext(Token::TYPE_WHILE);

        $this->assertAndNext(Token::TYPE_OPEN_PARENTHESIS);
        $condition = $this->parseExpression();
        $this->skipIf(Token::TYPE_SEMICOLON);
        $this->assertAndNext(Token::TYPE_CLOSE_PARENTHESIS);

        $this->assertAndNext(Token::TYPE_CURLY_OPEN);
        $statements = [];
        while ($this->current() && !$this->current()->is(Token::TYPE_CURLY_CLOSE)) {
            $statements[] = $this->parseStatement();
        }
        $this->assertAndNext(Token::TYPE_CURLY_CLOSE);

        return new \AST\WhileStatement($condition, $statements);
    }

    protected function parseFunctionStatement()
    {
        $this->assertAndNext(Token::TYPE_FUNCTION);

        $name = $this->parseIdentifier();

        $this->assertAndNext(Token::TYPE_OPEN_PARENTHESIS);
        $arguments = [];
        while ($this->current()->is(Token::TYPE_IDENTIFIER)) {
            $arguments[] = $this->parseIdentifier();
            $this->skipIf(Token::TYPE_COMMA);
        }
        $this->assertAndNext(Token::TYPE_CLOSE_PARENTHESIS);

        $this->assertAndNext(Token::TYPE_CURLY_OPEN);
        $statements = [];
        while ($this->current() && !$this->current()->is(Token::TYPE_CURLY_CLOSE)) {
            $statements[] = $this->parseStatement();
        }
        $this->assertAndNext(Token::TYPE_CURLY_CLOSE);

        return new \AST\FunctionStatement($name, $arguments, $statements);
    }

    protected function parserReturnStatement()
    {
        $this->assertAndNext(Token::TYPE_RETURN);

        return new \AST\ReturnStatement($this->parseExpression());
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

        $text = $this->current()->getValue();
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
                $callee = $result;
                $arguments = $this->parseCallExpressionArguments();
                $result = new AST\CallExpression($callee, $arguments);
                $this->assertToken(Token::TYPE_CLOSE_PARENTHESIS);
                $this->next();
            } elseif ($this->current()->is(Token::TYPE_OPEN_SQUARE_BRACKET)) {
                return $this->parseKeyExpression($result);
            } elseif ($this->current()->is(Token::TYPE_DOT)) {
                return $this->parseMemberExpression($result);
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
            Token::TYPE_QUOTE,
            Token::TYPE_DOUBLE_QUOTE,
            Token::TYPE_APOSTROPHE,
            Token::TYPE_EQUALS,
            Token::TYPE_TYPEOF,
            Token::TYPE_OPEN_SQUARE_BRACKET,
            Token::TYPE_DOT,
        ]);

        if ($this->current()->is([Token::TYPE_QUOTE, Token::TYPE_DOUBLE_QUOTE, Token::TYPE_APOSTROPHE])) {
            return $this->parseStringExpression();
        } elseif ($this->current()->is(Token::TYPE_TYPEOF)) {
            return $this->parseTypeofExpression();
        } elseif ($this->current()->is(Token::TYPE_OPEN_SQUARE_BRACKET)) {
            return $this->parseArrayExpression();
        } elseif ($this->current()->is(Token::TYPE_IDENTIFIER)) {
            if ($this->lookahead()->is(Token::TYPE_EQUALS)) {
                return $this->parseAssignExpression();
            }

            if ($this->lookahead()->is(Token::TYPE_OPEN_SQUARE_BRACKET)) {
                $result = $this->parseKeyExpression($this->parseIdentifier());
                if ($this->current()->is(Token::TYPE_EQUALS)) {
                    $this->next();
                    $expression = $this->parseExpression();
                    return new \AST\AssignExpression($result, $expression);
                }

                return $result;
            }

            if ($this->lookahead()->is(Token::TYPE_DOT)) {
                $result = $this->parseMemberExpression($this->parseIdentifier());
                if ($this->current()->is(Token::TYPE_EQUALS)) {
                    $this->next();
                    $expression = $this->parseExpression();
                    return new \AST\AssignExpression($result, $expression);
                }

                return $result;
            }

            return $this->parseIdentifier();
        }  elseif ($this->current()->is(Token::TYPE_OPEN_PARENTHESIS)) {
            return $this->parseParentheses();
        }

        return $this->parseLiteral();
    }


    protected function parseStringExpression()
    {
        $this->assertToken([Token::TYPE_QUOTE, Token::TYPE_DOUBLE_QUOTE, Token::TYPE_APOSTROPHE]);

        $quote = $this->current();
        $this->next();
        $this->assertToken(Token::TYPE_TEXT);

        $value = $this->current()->getValue();
        if ($quote->is(Token::TYPE_DOUBLE_QUOTE)) {
            // todo remove this dirty hack
            $value = str_replace(
                ['\r', '\n', '\t'],
                ["\r", "\n", "\t"],
                $value
            );
        }
        $node = new AST\StringExpression($value, $quote->getValue());

        $this->next();
        $this->assertToken($quote->getType());
        $this->next();

        return $node;
    }

    protected function parseArrayExpression()
    {
        $this->assertAndNext(Token::TYPE_OPEN_SQUARE_BRACKET);

        $items = [];
        $keys = [];
        while (true) {
            if ($this->current()->is(Token::TYPE_CLOSE_SQUARE_BRACKET)) {
                break;
            }

            $first = $this->parseExpression();
            if ($this->current()->is(Token::TYPE_COLON)) {
                $this->next();
                $keys[] = $first;
                $items[] = $this->parseExpression();
            } else {
                $keys[] = null;
                $items[] = $first;
            }

            if ($this->current()->is(Token::TYPE_COMMA)) {
                $this->next();
            } else {
                break;
            }
        }

        $this->assertAndNext(Token::TYPE_CLOSE_SQUARE_BRACKET);

        return new \AST\ArrayExpression($items, $keys);
    }

    protected function parseMemberExpression(\AST\ExpressionNode $owner)
    {
        $this->assertAndNext(Token::TYPE_DOT);

        $this->assertToken(Token::TYPE_IDENTIFIER);

        $value = $this->current()->getValue();
        $this->next();

        $name = new \AST\StringExpression($value, '\'');
        $result = new \AST\MemberExpression($owner, $name);

        if ($this->current()->is(Token::TYPE_OPEN_SQUARE_BRACKET)) {
            return $this->parseKeyExpression($result);
        }

        if ($this->current()->is(Token::TYPE_DOT)) {
            return $this->parseMemberExpression($result);
        }

        return $result;
    }

    protected function parseKeyExpression(\AST\ExpressionNode $owner)
    {
        $this->assertAndNext(Token::TYPE_OPEN_SQUARE_BRACKET);

        if ($this->current()->is(Token::TYPE_CLOSE_SQUARE_BRACKET)) {
            $this->next();
            $this->assertToken(Token::TYPE_EQUALS);
            return new \AST\PushExpression($owner);
        }

        $name = $this->parseExpression();

        $this->assertAndNext(Token::TYPE_CLOSE_SQUARE_BRACKET);

        $result = new \AST\MemberExpression($owner, $name);

        if ($this->current()->is(Token::TYPE_OPEN_SQUARE_BRACKET)) {
            return $this->parseKeyExpression($result);
        }

        if ($this->current()->is(Token::TYPE_DOT)) {
            return $this->parseMemberExpression($result);
        }

        return $result;
    }

    protected function parseTypeofExpression()
    {
        $this->assertToken(Token::TYPE_TYPEOF);
        $name = $this->current()->getValue();
        $this->next();
        $expression = $this->parseUnaryExpression();

        return new AST\TypeofExpression($name, $expression);
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

    protected function parseAssignExpression()
    {
        $this->assertToken(Token::TYPE_IDENTIFIER);
        $name = $this->parseIdentifier();
        $this->assertToken(Token::TYPE_EQUALS);
        $this->next();
        $expression = $this->parseExpression();

        return new \AST\AssignExpression($name, $expression);
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
                if ($this->current()->is(Token::TYPE_CLOSE_PARENTHESIS)) {
                    break;
                }
            }
        }

        return $arguments;
    }

    protected function assertAndNext($types)
    {
        $this->assertToken($types);
        $this->next();
    }

    protected function skipIf($types)
    {
        if ($this->current()->is($types)) {
            $this->next();
        }
    }

}