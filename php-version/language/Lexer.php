<?php

class Lexer
{
    protected $cursor = -1;
    protected $currentLine = 1;
    protected $currentLinePosition = 0;
    protected $sourceCode = '';
    protected $tokens = [];

    protected static $lexemes = [
        '+' => Token::TYPE_PLUS,
        '-' => Token::TYPE_MINUS,
        '/' => Token::TYPE_DIVISION,
        '*' => Token::TYPE_MULTIPLY,
        '(' => Token::TYPE_OPEN_PARENTHESIS,
        ')' => Token::TYPE_CLOSE_PARENTHESIS,
        '{' => Token::TYPE_CURLY_OPEN,
        '}' => Token::TYPE_CURLY_CLOSE,
        '.' => Token::TYPE_DOT,
        ',' => Token::TYPE_COMMA,
        '=' => Token::TYPE_EQUALS,
        'let' => Token::TYPE_LET,
        'var' => Token::TYPE_VAR,
        'instanceof' => Token::TYPE_INSTANCEOF,
        '^' => Token::TYPE_CARET,
    ];

    public function tokenize(string $sourceCode): array
    {
        $this->reset();
        $this->sourceCode = $sourceCode;

        while ($char = $this->next()) {
            if ($this->isEol($this->current())) {
                $this->currentLine++;
                $this->currentLinePosition = 0;
            }

            if ($this->isWhitespace($char)) {
                continue;
            }

            if ($this->isNumeric($char)) {
                $this->while([$this, 'isNumeric'], Token::TYPE_NUMBER);
                continue;
            }

            if ($this->isText($char)) {
                $this->while([$this, 'isText'], Token::TYPE_IDENTIFIER);
                continue;
            }

            if (array_key_exists($char, self::$lexemes)) {
                $this->token(self::$lexemes[$char], $char);
                continue;
            }
        }

        $this->token(Token::TYPE_EOF, '');

        return $this->tokens;
    }

    protected function while(callable $filter, $tokenType)
    {
        $value = $this->current();

        while (true) {
            $char = $this->next();

            if ($filter($char)) {
                $value .= $char;
            } else {
                $this->previous();
                break;
            }
        }

        if (array_key_exists($value, self::$lexemes)) {
            $tokenType = self::$lexemes[$value];
        }

        $this->token($tokenType, $value);
    }

    private function token($type, $value)
    {
        $this->tokens[] = new Token(
            $type,
            $value,
            $this->currentLinePosition - (mb_strlen($value) - 1),
            $this->currentLine
        );
    }

    protected function next()
    {
        if ($this->cursor >= mb_strlen($this->sourceCode)) {
            return null;
        }

        $this->cursor++;
        $this->currentLinePosition++;

        return $this->current();
    }

    protected function previous()
    {
        if ($this->cursor >= mb_strlen($this->sourceCode)) {
            return null;
        }

        $this->cursor--;
        $this->currentLinePosition--;

        return $this->current();
    }

    protected function current()
    {
        return $this->sourceCode[$this->cursor] ?? null;
    }

    protected function reset()
    {
        $this->cursor = -1;
        $this->currentLinePosition = 0;
        $this->currentLine = 1;
        $this->tokens = [];
    }

    protected function isEol($char)
    {
        return $char === "\n";
    }

    protected function isWhitespace($char)
    {
        return $this->isEol($char) || preg_match('/\s/ui', $char);
    }

    protected function isNumeric($char)
    {
        return preg_match('/\d/ui', $char);
    }

    protected function isText($char)
    {
        return preg_match('/[a-z]/ui', $char);
    }

    public function __toString()
    {
        $text = '';
        foreach ($this->tokens as $token) {
            $text .= (string)$token;
            $text .= PHP_EOL;
        }

        return $text;
    }

}