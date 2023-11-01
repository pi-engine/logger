<?php

namespace Logger\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface LogRepositoryInterface
{
    public function addUser(array $params = []): void;
    public function readInventoryLog(array $params = []): HydratingResultSet|array ;
}