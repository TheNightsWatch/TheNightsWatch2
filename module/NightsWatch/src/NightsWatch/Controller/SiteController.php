<?php

namespace NightsWatch\Controller;

use Doctrine\Common\Collections\Criteria;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;
use NightsWatch\Form\LoginForm;
use Zend\View\Model\ViewModel;

class SiteController extends AbstractActionController
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
        return;
    }

    public function loginAction()
    {
        return new ViewModel(
            [
                'form' => new LoginForm(),
            ]
        );
    }
}
