<?php

namespace Logger\Factory\Service;

use Logger\Repository\LoggerRepositoryInterface;
use Logger\Service\LoggerService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use User\Service\AccountService;

class LoggerServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return LoggerService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerService
    {
        $config = $container->get('config');

        return new LoggerService(
            $container->get(LoggerRepositoryInterface::class), 
            $config['log']
        );
    }
}
