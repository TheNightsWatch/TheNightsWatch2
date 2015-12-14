<?php

namespace NightsWatch\Navigation\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

class RightAuthNavigationFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'right-auth';
    }
}
