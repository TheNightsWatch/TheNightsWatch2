<?php

namespace NightsWatch;

return [
    'controllers' => [
        'invokables' => [
            'Site' => 'NightsWatch\Controller\SiteController',
            'Map' => 'NightsWatch\Controller\MapController',
            'Chat' => 'NightsWatch\Controller\ChatController',
            'Join' => 'NightsWatch\Controller\JoinController',
            'Rules' => 'NightsWatch\Controller\RulesController',
            'Announcement' => 'NightsWatch\Controller\AnnouncementController',
            'User' => 'NightsWatch\Controller\UserController',
            'Event' => 'NightsWatch\Controller\EventController',
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'segment',
                'options' => [
                    'route' => '[/][:controller][/:action]',
                    'defaults' => [
                        'controller' => 'site',
                        'action' => 'index',
                    ],
                ],
            ],
            'homePlural' => [
                'type' => 'segment',
                'options' => [
                    'route' => '[/][:controller]s[/:action]',
                    'defaults' => [
                        'action' => 'index',
                    ],
                ],
            ],
            'rules' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/rules[/:action]',
                    'defaults' => [
                        'controller' => 'rules',
                        'action' => 'index',
                    ],
                ],
            ],
            'mumble' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/mumble',
                    'defaults' => [
                        'controller' => 'site',
                        'action' => 'mumble',
                    ],
                ],
            ],
            'login' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => 'site',
                        'action' => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => 'site',
                        'action' => 'logout',
                    ],
                ],
            ],
            'calendar' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/calendar',
                    'defaults' => [
                        'controller' => 'event',
                        'action' => 'index',
                    ],
                ],
            ],
            'id' => [
                'type' => 'segment',
                'options' => [
                    'route' => '[/]:controller/[:id][/:action]',
                    'constraints' => [
                        'id' => '\d+',
                    ],
                    'defaults' => [
                        'action' => 'view',
                    ],
                ],
            ],
            'user' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/user/[:username][/:action]',
                    'defaults' => [
                        'controller' => 'user',
                        'action' => 'view',
                    ],
                ],
            ],
            'calendarDate' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/calendar/[:year]/[:month]/[:day]',
                    'constraints' => [
                        'year' => '\d+',
                        'month' => '\d+',
                        'day' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => 'event',
                        'action' => 'date',
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
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'right-noauth-navigation' => 'NightsWatch\Navigation\Service\RightNoAuthNavigationFactory',
            'right-auth-navigation' => 'NightsWatch\Navigation\Service\RightAuthNavigationFactory',
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
            ],
            [
                'label' => 'Announcements',
                'route' => 'homePlural',
                'controller' => 'announcement',
            ],
            [
                'label' => 'Calendar',
                'route' => 'calendar',
                'controller' => 'event',
            ],
            [
                'label' => 'Members',
                'route' => 'homePlural',
                'controller' => 'user',
            ],
            [
                'label' => 'Map',
                'route' => 'home',
                'controller' => 'map',
            ],
            [
                'label' => 'Rules',
                'route' => 'rules',
            ],
            [
                'label' => 'Mumble',
                'route' => 'mumble',
            ]
        ],
        'right-noauth' => [
            [
                'label' => 'Log In',
                'route' => 'login',
            ],
            [
                'label' => 'Register',
                'route' => 'home',
                'controller' => 'join',
            ]
        ],
        'right-auth' => [
            [
                'label' => 'Log Out',
                'route' => 'logout',
            ]
        ]
    ],
];
