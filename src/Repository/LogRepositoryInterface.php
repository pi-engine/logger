<?php

namespace Logger\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface LogRepositoryInterface
{
    public function addUserLog(array $params = []): void;

    public function getUserLogList(array $params = []): HydratingResultSet|array;

    public function getUserLogCount(array $params = []): int;

    public function getSystemLogList(array $params = []): HydratingResultSet|array;

    public function getSystemLogCount(array $params = []): int;

    public function cleanupSystemLog(int $limitation = 1000): void;
}