<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once '../init_autoloader.php';

$app = Zend\Mvc\Application::init(require 'application.config.php');

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $app->getServiceManager()->get('doctrine.entitymanager.orm_default');

return ConsoleRunner::createHelperSet($entityManager);
