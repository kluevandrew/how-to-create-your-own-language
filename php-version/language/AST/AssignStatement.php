<?php
/**
 * Created by PhpStorm.
 * User: msoft
 * Date: 18.06.17
 * Time: 23:45
 */

namespace AST;


class AssignStatement extends StatementNode
{
    protected $name;

    protected $value;

    /**
     * AssignStatement constructor.
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->toString([$this->name, $this->value]);
    }


}