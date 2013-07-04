<?php

return [
    'controllers' => [
        'invokables' => [
            'NightsWatch\Controller\Site' => 'NightsWatch\Controller\SiteController',
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
                        'controller' => 'NightsWatch\Controller\Site',
                        'action' => 'index',
                    ]
                ]
            ]
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'nights-watch/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
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
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];
