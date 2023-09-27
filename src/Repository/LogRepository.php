<?php

namespace Logger\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Sql;
use Laminas\Hydrator\HydratorInterface;
use Logger\Model\Inventory;
use Logger\Model\User;
use RuntimeException;

class LogRepository implements LogRepositoryInterface
{
    private string $tableLog = 'log_inventory';

    private string $tableUser = 'log_user';

    private AdapterInterface $db;

    private Inventory $inventoryPrototype;

    private User $userPrototype;

    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Inventory $inventoryPrototype,
        User $userPrototype,
    ) {
        $this->db                 = $db;
        $this->hydrator           = $hydrator;
        $this->inventoryPrototype = $inventoryPrototype;
        $this->userPrototype      = $userPrototype;
    }

    public function addUser(array $params = []): void
    {
        $insert = new Insert($this->tableUser);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
    }

}