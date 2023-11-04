<?php

namespace Logger\Factory\Repository;

use Audit\Model\Plan;
use Audit\Repository\AuditRepository;
use Interop\Container\Containerinterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Model\Inventory;
use Logger\Model\User;
use Logger\Repository\LogRepository;

class LogRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LogRepository
    {
        return new LogRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Inventory(0, 0,0,0,0, 0, 0, 0, 0),
            new User(0, 0,0,0,0,0,0, 0, 0, 0),
        );
    }
}