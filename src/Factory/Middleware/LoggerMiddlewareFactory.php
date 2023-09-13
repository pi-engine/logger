<?php

namespace Logger\Factory\Middleware;

use Improvement\Middleware\ImprovementAddMiddleware;
use Improvement\Service\ImprovementService;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Middleware\LoggerMiddleware;
use Logger\Service\LoggerService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use User\Handler\ErrorHandler;

class LoggerMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerMiddleware
    {
        return new LoggerMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ErrorHandler::class),
            $container->get(LoggerService::class)
        );
    }
}