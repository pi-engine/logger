<?php

namespace Logger\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface LoggerRepositoryInterface
{

    public function addLog(array $params): object|array;

    public function updateLog(array $params): object|array;

    public function getLogsCount();

    public function emptyLogTable(array $array);


}
