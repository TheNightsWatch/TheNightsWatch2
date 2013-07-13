<?php

namespace NightsWatch\Mvc\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\View\Model\ViewModel;

class ActionController extends AbstractActionController
{
    private $auth = null;

    /** @var \Doctrine\Orm\EntityManager */
    protected $entityManager;

    /** @var \NightsWatch\Entity\User */
    protected $identityEntity = -1;

    public function updateLayoutWithIdentity()
    {
        $this->layout()->setVariable('hasIdentity', $this->getAuthenticationService()->hasIdentity());
        $this->layout()->setVariable('identity', $this->getIdentityEntity());
    }

    /**
     * @return \Doctrine\Orm\EntityManager
     */
    public function getEntityManager()
    {
        if (is_null($this->entityManager)) {
            $this->entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->entityManager;
    }

    /**
     * @return \NightsWatch\Entity\User|null
     */
    public function getIdentityEntity()
    {
        if ($this->identityEntity === -1) {
            if ($this->getAuthenticationService()->hasIdentity()) {
                $identity = $this->getEntityManager()
                    ->find('NightsWatch\Entity\User', $this->getAuthenticationService()->getIdentity());
                $this->identityEntity = $identity;
                if (is_null($identity)) {
                    $this->getAuthenticationService()->clearIdentity();
                }
            } else {
                $this->identityEntity = null;
            }
        }
        return $this->identityEntity;
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        if (is_null($this->auth)) {
            $this->auth = new AuthenticationService();
            $this->auth->setStorage(new SessionStorage('NightsWatch\Auth'));
        }
        return $this->auth;
    }

    protected function disallowMember()
    {
        if ($this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home', ['controller' => 'chat']);
            return true;
        } else {
            return false;
        }
    }

    protected function disallowGuest()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('login');
            return true;
        } else {
            return false;
        }
    }

    protected function disallowRankLessThan($rank)
    {
        if ($this->disallowGuest()) {
            $this->redirect()->toRoute('login');
            return true;
        }
        if ($this->getIdentityEntity()->rank < $rank) {
            $this->redirect()->toRoute('home');
            return true;
        }
        return false;
    }

    protected function disallowRankGreaterThan($rank)
    {
        if ($this->getIdentityEntity() && $this->getIdentityEntity()->rank > $rank) {
            $this->redirect()->toRoute('home');
            return true;
        }
        return false;
    }
}
