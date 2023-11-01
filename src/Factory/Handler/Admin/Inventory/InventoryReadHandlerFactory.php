<?php

namespace Logger\Factory\Handler\Admin\Inventory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Logger\Handler\Admin\Inventory\InventoryReadHandler;
use Logger\Service\LoggerService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class InventoryReadHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return InventoryReadHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):InventoryReadHandler
    {
        return new InventoryReadHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(LoggerService::class)
        );
    }
}