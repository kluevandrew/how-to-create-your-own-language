<?php

namespace Interpreter;

class StdLib extends Scope
{

    /**
     * StdLib constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->scope = [
            'print' => new NativeFunction('print', 'printf'),
            'random' =>  new NativeFunction('print', 'random_int', 2),
        ];
    }

}