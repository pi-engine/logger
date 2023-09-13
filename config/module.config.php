<?php

namespace Logger;

return [
    'service_manager' => [
        'factories' => [
            Service\LoggerService::class       => Factory\Service\LoggerServiceFactory::class,
            Middleware\LoggerMiddleware::class => Factory\Middleware\LoggerMiddlewareFactory::class,
        ],
    ],
];