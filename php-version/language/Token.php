<?php

class Token
{
    const TYPE_MINUS = 'T_MINUS';
    const TYPE_PLUS = 'T_PLUS';
    const TYPE_DIVISION = 'T_DIVISION';
    const TYPE_OPEN_PAREN = 'T_OPEN_PAREN';
    const TYPE_MULTIPLY = 'T_MULTIPLY';
    const TYPE_CLOSE_PAREN = 'T_CLOSE_PAREN';
    const TYPE_CURLY_CLOSE = 'T_CURLY_CLOSE';
    const TYPE_CURLY_OPEN = 'T_CURLY_OPEN';
    const TYPE_DOT = 'T_DOT';
    const TYPE_COMMA = 'T_COMMA';
    const TYPE_NUMBER = 'T_NUMBER';
    const TYPE_IDENTIFIER = 'T_IDENTIFIER';
    const TYPE_LET = 'T_LET';
    const TYPE_VAR = 'T_VAR';
    const TYPE_EQUALS = 'T_EQUALS';
    const TYPE_EOF = 'T_EOF';

    protected $type;

    protected $value;

    protected $position;

    protected $line;

    /**
     * Token constructor.
     * @param $type
     * @param $value
     * @param $position
     * @param $line
     */
    public function __construct($type, $value, $position, $line)
    {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
        $this->line = $line;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    public function __toString()
    {
        return "$this->type<{$this->value}> at line {$this->line}:{$this->position}";
    }

    public function is($types)
    {
        return in_array($this->type, (array)$types);
    }

}