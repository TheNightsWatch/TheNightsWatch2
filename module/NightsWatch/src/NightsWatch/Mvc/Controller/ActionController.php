<?php

namespace NightsWatch\Mvc\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ActionController extends AbstractActionController
{
    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return parent::getRequest();
    }
}
