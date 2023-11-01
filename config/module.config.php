<?php

namespace Logger;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Logger\Middleware\LoggerRequestMiddleware;
use User\Middleware\AuthenticationMiddleware;
use User\Middleware\AuthorizationMiddleware;
use User\Middleware\SecurityMiddleware;
use User\Middleware\RawDataValidationMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\LogRepositoryInterface::class => Repository\LogRepository::class,
        ],
        'factories' => [
            Repository\LogRepository::class           => Factory\Repository\LogRepositoryFactory::class,
            Service\LoggerService::class              => Factory\Service\LoggerServiceFactory::class,
            Middleware\LoggerRequestMiddleware::class => Factory\Middleware\LoggerRequestMiddlewareFactory::class,
            Handler\Admin\InstallerHandler::class => Factory\Handler\Admin\InstallerHandlerFactory::class,
            Handler\Admin\Inventory\InventoryReadHandler::class => Factory\Handler\Admin\Inventory\InventoryReadHandlerFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            // Admin section
            'admin_content' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/admin/logger',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'installer' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/installer',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'admin',
                                'package' => 'installer',
                                'handler' => 'installer',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestMiddleware::class,
                                    Handler\Admin\InstallerHandler::class
                                ),
                            ],
                        ],
                    ],
                    'inventory' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/inventory',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'read' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/read',
                                    'defaults' => [
                                        'module' => 'logger',
                                        'section' => 'admin',
                                        'package' => 'inventory',
                                        'handler' => 'read',
                                        'permission' => 'admin-logger-inventory-read',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            //LoggerRequestMiddleware::class,
                                            Handler\Admin\Inventory\InventoryReadHandler::class
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