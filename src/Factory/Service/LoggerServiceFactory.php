<?php

namespace Logger\Factory\Service;

use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Repository\LogRepositoryInterface;
use Logger\Service\LoggerService;
use User\Service\AccountService;
use User\Service\UtilityService;

class LoggerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerService
    {
        $config = $container->get('config');
        $config = $config['logger'] ?? [];

        return new LoggerService(
            $container->get(LogRepositoryInterface::class),
            $container->get(UtilityService::class),
            $config
        );
    }
}
