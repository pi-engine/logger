<?php

namespace Logger\Factory\Repository;

use Logger\Model\Logger;
use Logger\Repository\LoggerRepository;
use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


class LoggerRepositoryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return LoggerRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerRepository
    {
        return new LoggerRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Logger(0, 0, 0, 0, 0, 0, '', '', 0, 0, 0),
        );
    }
}
