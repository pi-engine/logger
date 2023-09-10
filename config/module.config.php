<?php

namespace Logger;

return [
    'service_manager' => [
        'aliases'    => [
            Repository\LoggerRepositoryInterface::class => Repository\LoggerRepository::class,
        ],
        'factories'  => [
            Repository\LoggerRepository::class => Factory\Repository\LoggerRepositoryFactory::class,
            Service\LoggerService::class       => Factory\Service\LoggerServiceFactory::class,
        ],
        'invokables' => [
            Listener\LogListener::class => Listener\LogListener::class,
        ],
    ],
];