<?php


namespace AST;


class MemberExpression extends ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    protected $owner;

    /**
     * @var ExpressionNode
     */
    protected $member;

    /**
     * IdentifierExpression constructor.
     * @param ExpressionNode $owner
     * @param ExpressionNode $member
     */
    public function __construct(ExpressionNode $owner, ExpressionNode $member)
    {
        $this->owner = $owner;
        $this->member = $member;
    }

    /**
     * @return ExpressionNode
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return ExpressionNode
     */
    public function getMember()
    {
        return $this->member;
    }


    public function __toString()
    {
        return $this->toString([$this->owner, $this->member]);
    }
}