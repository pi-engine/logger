<?php

namespace Logger\Service;

use IntlDateFormatter;
use Laminas\Escaper\Escaper;
use NumberFormatter;
use Logger\Service\ServiceInterface;

class UtilityService implements ServiceInterface
{
    /* @var array */
    protected array $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function inventoryLogCanonize($object): array
    {
        return ['id' => -1];
    }

}