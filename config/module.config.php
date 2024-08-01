<?php

namespace Logger;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use User\Middleware\AuthenticationMiddleware;
use User\Middleware\AuthorizationMiddleware;
use User\Middleware\RequestPreparationMiddleware;
use User\Middleware\SecurityMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\LogRepositoryInterface::class => Repository\LogRepository::class,
        ],
        'factories' => [
            Repository\LogRepository::class                     => Factory\Repository\LogRepositoryFactory::class,
            Service\UtilityService::class                       => Factory\Service\UtilityServiceFactory::class,
            Service\LoggerService::class                        => Factory\Service\LoggerServiceFactory::class,
            Middleware\LoggerRequestMiddleware::class           => Factory\Middleware\LoggerRequestMiddlewareFactory::class,
            Middleware\LoggerRequestResponseMiddleware::class   => Factory\Middleware\LoggerRequestResponseMiddlewareFactory::class,
            Handler\InstallerHandler::class                     => Factory\Handler\InstallerHandlerFactory::class,
            Handler\Admin\Inventory\InventoryReadHandler::class => Factory\Handler\Admin\Inventory\InventoryReadHandlerFactory::class,
            Handler\Admin\User\UserReadHandler::class           => Factory\Handler\Admin\User\UserReadHandlerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            // Admin section
            'admin_logger' => [
                'type'         => Literal::class,
                'options'      => [
                    'route'    => '/admin/logger',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'installer' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/installer',
                            'defaults' => [
                                'module'     => 'erm',
                                'section'    => 'admin',
                                'package'    => 'installer',
                                'handler'    => 'installer',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
//                                    LoggerRequestMiddleware::class,
                                    Handler\InstallerHandler::class
                                ),
                            ],
                        ],
                    ],
                    'inventory' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/inventory',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'read' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/read',
                                    'defaults' => [
                                        'title'      => 'Admin logger user read',
                                        'module'     => 'logger',
                                        'section'    => 'admin',
                                        'package'    => 'inventory',
                                        'handler'    => 'read',
                                        'permission' => 'admin-logger-inventory-read',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Inventory\InventoryReadHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'user'      => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/user',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'read' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/read',
                                    'defaults' => [
                                        'title'      => 'Admin logger inventory read',
                                        'module'     => 'logger',
                                        'section'    => 'admin',
                                        'package'    => 'user',
                                        'handler'    => 'read',
                                        'permission' => 'admin-logger-user-read',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\User\UserReadHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];