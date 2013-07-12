<?php

namespace NightsWatch\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class ForceAdapter implements AdapterInterface
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function authenticate()
    {
        return new Result(
            Result::SUCCESS,
            $this->id,
            []
        );
    }
}
