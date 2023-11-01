<?php

namespace Logger\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
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
        AdapterInterface  $db,
        HydratorInterface $hydrator,
        Inventory         $inventoryPrototype,
        User              $userPrototype,
    )
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->inventoryPrototype = $inventoryPrototype;
        $this->userPrototype = $userPrototype;
    }

    public function addUser(array $params = []): void
    {
        $insert = new Insert($this->tableUser);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
    }

    public function readInventoryLog(array $params = []): HydratingResultSet|array
    {
        $where = $this->createConditional($params);
        $order = $params['order'] ?? ['timestamp DESC', 'id DESC'];
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableLog)->where($where)->order($order)->offset($params['offset'])->limit($params['limit']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->inventoryPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    public function getInventoryLogCount(array $params = []): int
    {
        $columns = ['count' => new Expression('count(*)')];
        $where = $this->createConditional($params);
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableLog)->columns($columns)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();
        return (int)$row['count'];
    }

    private function createConditional(array $params): array
    {
        $where = [];
        if (!empty($params['timestamp'])) {
            $where['timestamp'] = $params['timestamp'];
        }
        if (!empty($params['priority'])) {
            $where['priority'] = $params['priority'];
        }
        if (!empty($params['priorityName'])) {
            $where['priorityName'] = $params['priorityName'];
        }
        if (!empty($params['message'])) {
            $where['message'] = $params['message'];
        }
        if (!empty($params['extra_data'])) {
            $where['extra_data LIKE ?'] = '%' . $params['extra_data'] . '%';
        }
        return $where;
    }

}