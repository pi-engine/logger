<?php

namespace Logger\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface LogRepositoryInterface
{
    public function addUser(array $params = []): void;

    public function getUserList(array $params = []): HydratingResultSet|array;

    public function getUserCount(array $params = []): int;

    public function readInventoryLog(array $params = []): HydratingResultSet|array;

    public function cleanup(int $limitation = 1000): void;
}