<?php

namespace Logger\Factory\Repository;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Model\System;
use Logger\Model\User;
use Logger\Repository\LogRepository;

class LogRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LogRepository
    {
        return new LogRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new System('', 0, '', '', 0, 0, 0, '', '','','','',0),
            new User(0, 0, 0, '', '', '', '', '', '', '','','','', 0),
        );
    }
}