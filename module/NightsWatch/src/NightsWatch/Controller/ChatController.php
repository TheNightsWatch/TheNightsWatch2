<?php

namespace NightsWatch\Controller;

use NightsWatch\Entity\ChatToken;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Entity\User;
use Zend\View\Model\JsonModel;
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

    public function tokenAction()
    {
        $em = $this->getEntityManager();
        $token = new ChatToken();
        $token->user = $this->getIdentityEntity();
        $token->generateToken();
        $em->persist($token);
        $em->flush();
        return new JsonModel(
            [
                'token' => $token->token,
            ]
        );
    }
}
