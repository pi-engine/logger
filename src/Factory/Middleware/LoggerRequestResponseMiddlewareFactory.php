<?php

namespace Logger\Factory\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Middleware\LoggerRequestResponseMiddleware;
use Logger\Service\LoggerService;
use Pi\Core\Handler\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class LoggerRequestResponseMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerRequestResponseMiddleware
    {
        return new LoggerRequestResponseMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ErrorHandler::class),
            $container->get(LoggerService::class)
        );
    }
}