<?php

class Lexer
{
    protected $cursor = -1;
    protected $currentLine = 1;
    protected $currentLinePosition = 0;
    protected static $lexemes = [
        '+' => Token::TYPE_PLUS,
        '++' => Token::TYPE_DOUBLE_PLUS,
        '-' => Token::TYPE_MINUS,
        '--' => Token::TYPE_DOUBLE_MINUS,
        '/' => Token::TYPE_SLASH,
        '\\' => Token::TYPE_BACK_SLASH,
        '*' => Token::TYPE_STAR,
        '**' => Token::TYPE_DOUBLE_STAR,
        '(' => Token::TYPE_OPEN_PARENTHESIS,
        ')' => Token::TYPE_CLOSE_PARENTHESIS,
        '{' => Token::TYPE_CURLY_OPEN,
        '}' => Token::TYPE_CURLY_CLOSE,
        '.' => Token::TYPE_DOT,
        ',' => Token::TYPE_COMMA,
        '=' => Token::TYPE_EQUALS,
        '==' => Token::TYPE_DOUBLE_EQUALS,
        '===' => Token::TYPE_TRIPLE_EQUALS,
        '^' => Token::TYPE_CARET,
        '^^' => Token::TYPE_DOUBLE_CARET,
        '->' => Token::TYPE_ARROW_RIGHT,
        '<-' => Token::TYPE_ARROW_LEFT,
        '<' => Token::TYPE_LT,
        '<=' => Token::TYPE_LTE,
        '>' => Token::TYPE_GT,
        '>=' => Token::TYPE_GTE,
        '<=>' => Token::TYPE_UFO,
        '\'' => Token::TYPE_QUOTE,
        '"' => Token::TYPE_DOUBLE_QUOTE,
        '&' => Token::TYPE_AMPERSAND,
        '&&' => Token::TYPE_DOUBLE_AMPERSAND,
        '|' => Token::TYPE_BAR,
        '||' => Token::TYPE_DOUBLE_BAR,
        ':' => Token::TYPE_COLON,
        ';' => Token::TYPE_SEMICOLON,
    ];
    protected $sourceCode = '';

    protected static $keywords = [
        'let' => Token::TYPE_LET,
        'var' => Token::TYPE_VAR,
        'if' => Token::TYPE_IF,
        'else' => Token::TYPE_ELSE,
        'elseif' => Token::TYPE_ELSE_IF,
        'and' => Token::TYPE_AND,
        'or' => Token::TYPE_OR,
        'for' => Token::TYPE_FOR,
        'while' => Token::TYPE_WHILE,
    ];

    protected $tokens = [];

    public function tokenize(string $sourceCode): array
    {
        $this->reset();
        $this->sourceCode = $sourceCode;

        while (null !== ($char = $this->next())) {
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

            if ($this->isQuote($char)) {
                $this->token(self::$lexemes[$char], $char);
                $this->next();
                $this->readInQuotes($char, Token::TYPE_TEXT);
                $this->assert($char);
                $this->token(self::$lexemes[$char], $char);
                continue;
            }

            $trio = $char . $this->lookahead(1) . $this->lookahead(2);
            if (array_key_exists($trio, self::$lexemes)) {
                $this->token(self::$lexemes[$trio], $trio);
                $this->next();
                $this->next();
                continue;
            }

            $pair = $char . $this->lookahead();
            if (array_key_exists($pair, self::$lexemes)) {
                $this->token(self::$lexemes[$pair], $pair);
                $this->next();
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

    protected function while (callable $filter, $tokenType)
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

        $lowerValue = strtolower($value);
        if (array_key_exists($lowerValue, self::$keywords)) {
            $tokenType = self::$keywords[$value];
            $value = $lowerValue;
        }

        $this->token($tokenType, $value);
    }

    protected function readInQuotes($compare, $tokenType)
    {
        $value = $this->current();

        while (true) {
            $char = $this->next();
            if ($compare === $char) {
                break;
            }
            $value .= $char;
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

    protected function lookahead($ahead = 1)
    {
        return $this->sourceCode[$this->cursor + $ahead] ?? null;
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

    protected function isQuote($char)
    {
        return in_array($char, ['"', "'", '`']);
    }

    protected function assert($char)
    {
        if ($this->current() !== $char) {
            throw new \RuntimeException("Unexpected char {$char}");
        }
    }

}