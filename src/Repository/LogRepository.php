<?php

namespace Logger\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;
use Logger\Model\Log;

class LogRepository implements LogRepositoryInterface
{
    private string $tablePlan = 'log';

    private AdapterInterface $db;

    private Log $logPrototype;

    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Log $logPrototype,
    ) {
        $this->db            = $db;
        $this->hydrator      = $hydrator;
        $this->logPrototype = $logPrototype;
    }

}