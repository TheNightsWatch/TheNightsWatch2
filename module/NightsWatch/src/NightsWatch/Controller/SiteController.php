<?php

namespace NightsWatch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SiteController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}
