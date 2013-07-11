<?php

namespace NightsWatch\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class ForceAdapter implements AdapterInterface
{
    public function __construct($username, $password)
    {
    }

    public function authenticate()
    {
        return Result::SUCCESS;
    }
}
