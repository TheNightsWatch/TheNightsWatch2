<?php

namespace NightsWatch\Controller;

use NightsWatch\Mvc\Controller\ActionController;

class RulesController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();
        return;
    }
}
