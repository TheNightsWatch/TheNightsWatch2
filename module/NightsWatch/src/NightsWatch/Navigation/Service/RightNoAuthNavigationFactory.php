<?php

namespace NightsWatch\Navigation\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

class RightNoAuthNavigationFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'right-noauth';
    }
}
