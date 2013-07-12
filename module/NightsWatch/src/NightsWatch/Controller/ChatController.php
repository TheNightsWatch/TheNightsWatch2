<?php

namespace NightsWatch\Controller;

use NightsWatch\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

class ChatController extends ActionController
{
    public function indexAction()
    {
        if ($this->disallowGuest()) {
            return false;
        }
        $this->updateLayoutWithIdentity();
        return new ViewModel([]);
    }
}
