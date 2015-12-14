<?php

namespace NightsWatch;

return [
    'controllers'     => [
        'invokables' => [
            'Site'         => 'NightsWatch\Controller\SiteController',
            'Map'          => 'NightsWatch\Controller\MapController',
            'Chat'         => 'NightsWatch\Controller\ChatController',
            'Join'         => 'NightsWatch\Controller\JoinController',
            'Rules'        => 'NightsWatch\Controller\RulesController',
            'Announcement' => 'NightsWatch\Controller\AnnouncementController',
            'User'         => 'NightsWatch\Controller\UserController',
            'Event'        => 'NightsWatch\Controller\EventController',
            'Black'        => 'NightsWatch\Controller\BlackController',
            'Mod'          => 'NightsWatch\Controller\ModController',
        ],
    ],
    'router'          => [
        'routes' => [
            'home'         => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '[/][:controller][/:action]',
                    'defaults' => [
                        'controller' => 'site',
                        'action'     => 'index',
                    ],
                ],
            ],
            'homePlural'   => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '[/][:controller]s[/:action]',
                    'defaults' => [
                        'action' => 'index',
                    ],
                ],
            ],
            'shotbowlogin' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/shotbowlogin',
                    'defaults' => [
                        'controller' => 'site',
                        'action'     => 'shotbowlogin',
                    ],
                ],
            ],
            'rules'        => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/rules[/:action]',
                    'defaults' => [
                        'controller' => 'rules',
                        'action'     => 'index',
                    ],
                ],
            ],
            'mumble'       => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/mumble',
                    'defaults' => [
                        'controller' => 'site',
                        'action'     => 'mumble',
                    ],
                ],
            ],
            'mod'          => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/mod',
                    'defaults' => [
                        'controller' => 'site',
                        'action'     => 'mod',
                    ],
                ],
            ],
            'login'        => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => 'site',
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout'       => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => 'site',
                        'action'     => 'logout',
                    ],
                ],
            ],
            'calendar'     => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/calendar',
                    'defaults' => [
                        'controller' => 'event',
                        'action'     => 'index',
                    ],
                ],
            ],
            'id'           => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '[/]:controller/[:id][/:action]',
                    'constraints' => [
                        'id' => '\d+',
                    ],
                    'defaults'    => [
                        'action' => 'view',
                    ],
                ],
            ],
            'user'         => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/user/[:username][/:action]',
                    'defaults' => [
                        'controller' => 'user',
                        'action'     => 'view',
                    ],
                ],
            ],
            'calendarDate' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/calendar/[:year]/[:month]/[:day]',
                    'constraints' => [
                        'year'  => '\d+',
                        'month' => '\d+',
                        'day'   => '\d+',
                    ],
                    'defaults'    => [
                        'controller' => 'event',
                        'action'     => 'date',
                    ],
                ],
            ],
            'takeTheBlack' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/taketheblack/[:action]',
                    'defaults' => [
                        'controller' => 'black',
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager'    => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack'      => [
            __DIR__.'/../view',
        ],
        'strategies'               => [
            'ViewJsonStrategy',
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories'          => [
            'navigation'              => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'right-noauth-navigation' => 'NightsWatch\Navigation\Service\RightNoAuthNavigationFactory',
            'right-auth-navigation'   => 'NightsWatch\Navigation\Service\RightAuthNavigationFactory',
        ],
        'aliases'            => [
            'translator' => 'MvcTranslator',
        ],
    ],
    'translator'      => [
        'locale'                    => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__.'/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'doctrine'        => [
        'driver' => [
            __NAMESPACE__.'_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__.'/../src/'.__NAMESPACE__.'/Entity'],
            ],
            'orm_default'             => [
                'drivers' => [
                    __NAMESPACE__.'\Entity' => __NAMESPACE__.'_driver',
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default'      => [
            [
                'label'      => 'Home',
                'route'      => 'home',
                'controller' => 'site',
                'action'     => 'index',
            ],
            [
                'label'      => 'Chat',
                'route'      => 'home',
                'controller' => 'chat',
            ],
            [
                'label'      => 'Announcements',
                'route'      => 'homePlural',
                'controller' => 'announcement',
            ],
            [
                'label'      => 'Calendar',
                'route'      => 'calendar',
                'controller' => 'event',
            ],
            [
                'label'      => 'Map',
                'route'      => 'home',
                'controller' => 'map',
            ],
            [
                'label' => 'Information',
                'route' => 'mod',
                'pages' => [
                    [
                        'label' => 'Rules',
                        'route' => 'rules',
                    ],
                    [
                        'label'      => 'Members',
                        'route'      => 'homePlural',
                        'controller' => 'user',
                    ],
                    [
                        'label' => 'Mumble',
                        'route' => 'mumble',
                    ],
                    [
                        'label' => 'Mod',
                        'route' => 'mod',
                    ],
                    [
                        'label' => 'Subreddit',
                        'uri'   => 'https://reddit.com/r/TheNightsWatch',
                    ],
                ],
            ],
        ],
        'right-noauth' => [
            [
                'label' => 'Login or Register',
                'route' => 'shotbowlogin',
            ],
        ],
        'right-auth'   => [
            [
                'label' => 'Log Out',
                'route' => 'logout',
            ],
        ],
    ],
];
