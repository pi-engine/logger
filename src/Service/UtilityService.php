<?php

namespace Logger\Service;

use IntlDateFormatter;
use Laminas\Escaper\Escaper;
use NumberFormatter;
use Logger\Service\ServiceInterface;

class UtilityService implements ServiceInterface
{
    public function __construct( )
    {
    }

    public function inventoryLogListCanonize($objectList): array
    {
        $list = [];
        foreach ($objectList as $object) {
            $list[] = $this->inventoryLogCanonize($object);
        }
        return array_values($list);
    }

    public function inventoryLogCanonize($object): array
    {
        return ['id' => -1];
    }

}