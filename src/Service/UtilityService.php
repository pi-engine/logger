<?php

namespace Logger\Service;

use IntlDateFormatter;
use Laminas\Escaper\Escaper;
use NumberFormatter;
use Logger\Service\ServiceInterface;

class UtilityService implements ServiceInterface
{
    public function __construct()
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
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id' => (int)$object->getId(),
                'timestamp' => $object->getTimestamp(),
                'priority' => $object->getPriority(),
                'priority_name' => $object->getPriorityName(),
                'message' => $object->getMessage(),
                'extra_data' => $object->getExtraData(),
            ];
        } else {
            $object = [
                'id' => (int)$object['id'],
                'timestamp' => $object['timestamp'],
                'priority' => $object['priority'],
                'priority_name' => $object['priorityName'],
                'message' => $object['message'],
                'extra_data' => $object['extra_data']
            ];
        }
        $object['extra_data'] = json_decode($object['extra_data'], true);
        return $object;

    }

}