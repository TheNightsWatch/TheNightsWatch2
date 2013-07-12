<?php

namespace NightsWatch\Controller;

use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Entity\User;
use Zend\View\Model\ViewModel;

class ChatController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();
        return new ViewModel(
            [
                'identity' => $this->getIdentityEntity(),
            ]
        );
    }
}
