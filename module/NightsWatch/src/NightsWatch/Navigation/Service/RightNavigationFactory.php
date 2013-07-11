<?php
namespace NightsWatch\Navigation\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

class RightNavigationFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'right';
    }
}
