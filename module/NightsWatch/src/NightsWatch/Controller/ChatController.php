<?php

namespace NightsWatch\Controller;

use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Entity\User;
use Zend\View\Model\ViewModel;

class ChatController extends ActionController
{
    public function indexAction()
    {
        if ($this->disallowRankLessThan(User::RANK_PRIVATE)) {
            return false;
        }
        $this->updateLayoutWithIdentity();
        return new ViewModel([]);
    }
}
