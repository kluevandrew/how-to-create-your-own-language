<?php


namespace AST;


class PushExpression extends ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    protected $owner;

    /**
     * IdentifierExpression constructor.
     *
     * @param ExpressionNode $owner
     */
    public function __construct(ExpressionNode $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return ExpressionNode
     */
    public function getOwner(): ExpressionNode
    {
        return $this->owner;
    }


    public function __toString()
    {
        return $this->toString([$this->owner]);
    }

}