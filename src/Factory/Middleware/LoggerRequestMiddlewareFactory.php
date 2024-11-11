<?php

namespace Logger\Factory\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Middleware\LoggerRequestMiddleware;
use Logger\Service\LoggerService;
use Pi\Core\Handler\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class LoggerRequestMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerRequestMiddleware
    {
        return new LoggerRequestMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ErrorHandler::class),
            $container->get(LoggerService::class)
        );
    }
}