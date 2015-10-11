<?php

namespace NightsWatch\Mvc\Controller;

use NightsWatch\Entity\Ip;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\View\Model\ViewModel;

class ActionController extends AbstractActionController
{
    private $auth = null;

    /** @var \Doctrine\Orm\EntityManager */
    protected $entityManager;

    /** @var \NightsWatch\Entity\User */
    protected $identityEntity = -1;

    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        $controller = $this;

        $events->attach(
            'dispatch',
            function ($event) use ($controller) {
                /** @var \Zend\Http\Request $request */
                $request = $event->getRequest();
                $addresses = $request->getServer()->get('HTTP_X_FORWARDED_FOR');
                $addresses = explode(',', $addresses);
                $address = end($addresses);
                $address = trim($address);
                $user = $controller->getIdentityEntity();
                if (!is_null($user)) {
                    $ip = $this->getEntityManager()
                        ->getRepository('NightsWatch\Entity\Ip')
                        ->findOneBy(['user' => $user, 'ip' => $address]);

                    if (!$ip) {
                        $ip = new Ip();
                        $ip->ip = $address;
                        $ip->user = $user;
                    }

                    $ip->lastSeen = new \DateTime();
                    $controller->getEntityManager()->persist($ip);
                    try {
                        $controller->getEntityManager()->flush();
                    } catch (\Exception $e) {
                        // The record already exists.  For now, do nothing.
                    }
                }
            },
            100
        );
    }

    public function updateLayoutWithIdentity()
    {
        $this->layout()->setVariable('identity', $this->getIdentityEntity());
        $this->layout()->setVariable('hasIdentity', !is_null($this->getIdentityEntity()));
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
    public function getIdentityEntity($refresh = false)
    {
        if ($this->identityEntity === -1) {
            if ($this->getAuthenticationService()->hasIdentity()) {
                $identity = $this->getEntityManager()
                    ->find('NightsWatch\Entity\User', $this->getAuthenticationService()->getIdentity());
                $this->identityEntity = $identity;
                if (is_null($identity) || $identity->banned) {
                    $this->getAuthenticationService()->clearIdentity();
                    $this->identityEntity = null;
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
     * @return \Zend\Http\Response
     */
    public function getResponse()
    {
        return parent::getResponse();
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
