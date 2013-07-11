<?php

namespace NightsWatch;

return [
    'controllers' => [
        'invokables' => [
            'Site' => 'NightsWatch\Controller\SiteController',
            'Map' => 'NightsWatch\Controller\MapController',
            'Chat' => 'NightsWatch\Controller\ChatController',
            'Join' => 'NightsWatch\Controller\JoinController',
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
        'factories' => [
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
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
    'navigation' => [
        'default' => [
            [
                'label' => 'Home',
                'route' => 'home',
                'controller' => 'site',
                'action' => 'index',
            ],
            [
                'label' => 'Chat',
                'route' => 'home',
                'controller' => 'chat',
                'action' => 'index',
            ]
        ]
    ]
];
