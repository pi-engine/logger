<?php

namespace Logger;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use User\Middleware\AuthenticationMiddleware;
use User\Middleware\AuthorizationMiddleware;
use User\Middleware\SecurityMiddleware;

return [
    'service_manager' => [
        'aliases' => [
            Repository\LoggerRepositoryInterface::class => Repository\LoggerRepository::class,
        ],
        'factories' => [
            Repository\LoggerRepository::class => Factory\Repository\LoggerRepositoryFactory::class,
            Service\LoggerService::class => Factory\Service\LoggerServiceFactory::class,
            Middleware\ValidationMiddleware::class => Factory\Middleware\ValidationMiddlewareFactory::class,
            Validator\SlugValidator::class => Factory\Validator\SlugValidatorFactory::class,
            Validator\TypeValidator::class => Factory\Validator\TypeValidatorFactory::class,
            Handler\ListHandler::class => Factory\Handler\ListHandlerFactory::class,


        ],
    ],


    'router' => [
        'routes' => [

            'log' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/log',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'list' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/list',
                            'defaults' => [
                                'module' => 'log',
                                'section' => 'admin',
                                'package' => 'log',
                                'handler' => 'list',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\ListHandler::class
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