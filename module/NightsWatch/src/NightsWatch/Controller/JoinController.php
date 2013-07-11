<?php

namespace NightsWatch\Controller;

use Doctrine\ORM\EntityManager;
use NightsWatch\Form\RegisterForm;
use NightsWatch\Form\VerifyForm;
use Zend\Mvc\Controller\AbstractActionController;
use Navarr\MinecraftAPI\MinecraftAPI;
use Zend\View\Model\ViewModel;

class JoinController extends AbstractActionController
{
    /** @var EntityManager */
    protected $entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if (is_null($this->entityManager)) {
            $this->entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->entityManager;
    }

    public function indexAction()
    {
        return new ViewModel(
            [
                'form' => new RegisterForm(),
            ]
        );
    }

    public function verifyAction()
    {
        return new ViewModel(
            [
                'form' => new VerifyForm(),
            ]
        );
    }
}
