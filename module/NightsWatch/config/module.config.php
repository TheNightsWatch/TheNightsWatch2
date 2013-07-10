<?php

namespace NightsWatch;

return [
    'controllers' => [
        'invokables' => [
            'Site' => 'NightsWatch\Controller\SiteController',
            'Map' => 'NightsWatch\Controller\MapController',
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'segment',
                'options' => [
                    'route' => '[/][:controller][/:action]',
                    'defaults' => [
                        '__NAMESPACE__' => 'NightsWatch\Controller',
                        'controller' => 'Site',
                        'action' => 'index',
                    ]
                ]
            ],
            // Specific Routes
            'join' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/join[/]',
                    'defaults' => [
                        '__NAMESPACE__' => 'NightsWatch\Controller',
                        'controller' => 'Site',
                        'action' => 'join',
                    ]
                ]
            ],
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'aliases' => [
            'translator' => 'MvcTranslator',
        ],
    ],
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ],
            ],
        ],
    ],
];
