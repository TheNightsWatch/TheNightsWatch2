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

    public function updateLayoutWithIdentity()
    {
        $this->layout()->setVariable('hasIdentity', $this->getAuthenticationService()->hasIdentity());
        $this->layout()->setVariable('identity', null);
        if ($this->getAuthenticationService()->hasIdentity()) {
            $identity = $this->getEntityManager()
                ->find('NightsWatch\Entity\User', $this->getAuthenticationService()->getIdentity());
            $this->layout()->setVariable('identity', $identity);
        }
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
}
