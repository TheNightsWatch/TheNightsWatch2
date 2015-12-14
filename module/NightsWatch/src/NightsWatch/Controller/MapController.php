<?php

namespace NightsWatch\Controller;

use NightsWatch\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

class MapController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();

        return new ViewModel();
    }
}
