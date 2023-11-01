<?php

namespace Logger\Factory\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Logger\Service\UtilityService;

class UtilityServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return UtilityService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UtilityService
    {
        return new UtilityService();
    }
}