<?php

namespace AST;

class ArrayExpression extends ExpressionNode
{
    protected $items = [];
    protected $keys = [];

    /**
     * CallExpression constructor.
     *
     * @param ExpressionNode[] $items
     * @param ExpressionNode[]|null[] $keys
     */
    public function __construct(array $items, array $keys)
    {
        $this->items = $items;
        $this->keys = $keys;
    }

    /**
     * @return ExpressionNode[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array|ExpressionNode[]|\null[]
     */
    public function getKeys()
    {
        return $this->keys;
    }


    public function __toString()
    {
        return $this->toString([$this->items]);
    }

}