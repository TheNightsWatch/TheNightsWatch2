<?php

namespace NightsWatch\Controller;

use NightsWatch\Entity\User;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\TakeTheBlack;

class BlackController extends ActionController
{
    public function indexAction()
    {
        if ($this->disallowGuest()) {
            return false;
        }
        $this->updateLayoutWithIdentity();
    }

    public function downloadAction()
    {
        if ($this->disallowGuest()) {
            return false;
        }
        $keepHat = (bool) intval($this->params()->fromQuery('keepHat', 0));

        $ident = $this->getIdentityEntity();

        $type = 'template'; // Recruit
        if ($ident->rank == User::RANK_COMMANDER) {
            $type = 'commander';
        } elseif ($ident->order == User::ORDER_RANGER) {
            $type = 'ranger';
        } elseif ($ident->order == User::ORDER_STEWARD) {
            $type = 'steward';
        }
        $name = $ident->username;

        $takeTheBlack = TakeTheBlack::load($name)
            ->template($type)
            ->keepHat($keepHat);

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()
            ->addHeaderLine('Content-Disposition', 'attachment; filename=take_the_black.png')
            ->addHeaderLine('Content-Type', 'image/png');
        $response->setContent($takeTheBlack->get());

        return $response;
    }
}
