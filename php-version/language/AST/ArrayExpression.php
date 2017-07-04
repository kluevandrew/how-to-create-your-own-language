<?php

namespace AST;

class ArrayExpression extends ExpressionNode
{
    protected $items = [];

    /**
     * CallExpression constructor.
     *
     * @param ExpressionNode[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return ExpressionNode[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function __toString()
    {
        return $this->toString([$this->items]);
    }

}