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
            Repository\LogRepository::class                   => Factory\Repository\LogRepositoryFactory::class,
            Service\LoggerService::class                      => Factory\Service\LoggerServiceFactory::class,
            Middleware\LoggerRequestMiddleware::class         => Factory\Middleware\LoggerRequestMiddlewareFactory::class,
            Middleware\LoggerRequestResponseMiddleware::class => Factory\Middleware\LoggerRequestResponseMiddlewareFactory::class,
            Handler\InstallerHandler::class                   => Factory\Handler\InstallerHandlerFactory::class,
            Handler\Admin\System\ListHandler::class           => Factory\Handler\Admin\System\ListHandlerFactory::class,
            Handler\Admin\User\ListHandler::class             => Factory\Handler\Admin\User\ListHandlerFactory::class,
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
                    'system' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/system',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'read' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'      => 'Admin logger user read',
                                        'module'     => 'logger',
                                        'section'    => 'admin',
                                        'package'    => 'system',
                                        'handler'    => 'read',
                                        'permission' => 'admin-logger-system-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\System\ListHandler::class
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
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'      => 'Admin logger user list',
                                        'module'     => 'logger',
                                        'section'    => 'admin',
                                        'package'    => 'user',
                                        'handler'    => 'list',
                                        'permission' => 'admin-logger-user-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\User\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    // Admin installer
                    'installer' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/installer',
                            'defaults' => [
                                'module'     => 'logger',
                                'section'    => 'admin',
                                'package'    => 'installer',
                                'handler'    => 'installer',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    Handler\InstallerHandler::class
                                ),
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