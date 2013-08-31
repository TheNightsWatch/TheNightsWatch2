<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Navarr
 * Date: 7/29/13
 * Time: 6:48 PM
 * To change this template use File | Settings | File Templates.
 */

namespace NightsWatch\Controller;


use NightsWatch\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

class UserController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();

        /** @var \NightsWatch\Entity\User $users */
        $users = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\User')
            ->findAll();

        $ranks = [];

        foreach ($users as $user) {
            if (!isset($ranks[$user->rank])) {
                $ranks[$user->rank] = [];
            }
            $ranks[$user->rank][] = $user;
        }

        return new ViewModel(['usersByRank' => $ranks]);
    }

    public function viewAction($username = "")
    {
        $this->updateLayoutWithIdentity();

        $username = $this->params()->fromRoute('username');
        $user = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel(['user' => $user]);
    }
}
