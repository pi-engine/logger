<?php

namespace Logger\Factory\Validator;

use Logger\Validator\TypeValidator;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TypeValidatorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return TypeValidator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeValidator
    {
        return new TypeValidator();
    }
}