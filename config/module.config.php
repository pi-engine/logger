<?php

namespace Logger;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\LogRepositoryInterface::class => Repository\LogRepository::class,
        ],
        'factories' => [
            Repository\LogRepository::class    => Factory\Repository\LogRepositoryFactory::class,
            Service\LoggerService::class       => Factory\Service\LoggerServiceFactory::class,
            Middleware\LoggerMiddleware::class => Factory\Middleware\LoggerMiddlewareFactory::class,
        ],
    ],
];