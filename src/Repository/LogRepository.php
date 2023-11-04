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

    private string $tableAccount = 'user_account';

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

    public function getUserList(array $params = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $where[' 1>0 AND log.information LIKE ?'] = '%"REQUEST_METHOD": "%' . $params['method'] . '%';
        }
        if (isset($params['ip']) && !empty($params['ip'])) {
            $where['2>1 AND log.information LIKE ?'] = '%"REMOTE_ADDR": "%' . $params['ip'] . '%';
        }
        if (isset($params['role']) && !empty($params['role'])) {
            $where[' 3>2 AND log.information LIKE ?'] = '%"' . $params['role'] . '"%';
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $where[' 4>3 AND log.information LIKE ?'] = '%"identity": "%' . $params['identity'] . '%';
        }
        if (!empty($params['data_from'])) {
            $where['time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['time_create <= ?'] = $params['data_to'];
        }

        $sql = new Sql($this->db);
        $from = ['log' => $this->tableUser];
        $select = $sql->select()->from($from)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name' => 'name',
                'user_email' => 'email',
                'user_mobile' => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getUserCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where = [];
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $where[' 1>0 AND log.information LIKE ?'] = '%"REQUEST_METHOD": "%' . $params['method'] . '%';
        }
        if (isset($params['ip']) && !empty($params['ip'])) {
            $where['2>1 AND log.information LIKE ?'] = '%"REMOTE_ADDR": "%' . $params['ip'] . '%';
        }
        if (isset($params['role']) && !empty($params['role'])) {
            $where[' 3>2 AND log.information LIKE ?'] = '%"' . $params['role'] . '"%';
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $where[' 4>3 AND log.information LIKE ?'] = '%"identity": "%' . $params['identity'] . '%';
        }
        if (!empty($params['data_from'])) {
            $where['time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['time_create <= ?'] = $params['data_to'];
        }

        $sql = new Sql($this->db);
        $from = ['log' => $this->tableUser];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function readInventoryLog(array $params = []): HydratingResultSet|array
    {
        $where = $this->createConditional($params);
        ///The name of this parameter is different in the database [log_user , log_inventory] and should not be in the createCondition
        if (isset($params['user_id']) & !empty($params['user_id'])) {
            $where['extra_user_id'] = $params['user_id'];
        }
        if (!empty($params['data_from'])) {
            $where['extra_time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['extra_time_create <= ?'] = $params['data_to'];
        }

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
        ///The name of this parameter is different in the database [log_user , log_inventory] and should not be in the createCondition
        if (isset($params['user_id']) & !empty($params['user_id'])) {
            $where['extra_user_id'] = $params['user_id'];
        }
        if (!empty($params['data_from'])) {
            $where['extra_time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['extra_time_create <= ?'] = $params['data_to'];
        }
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
            $where['priority like ?'] = '%' . $params['priority'] . '%';
        }
        if (!empty($params['priority_name'])) {
            $where['priorityName like ?'] = '%' . $params['priority_name'] . '%';
        }
        if (!empty($params['message'])) {
            $where['message like ?'] = '%' . $params['message'] . '%';
        }
        if (!empty($params['method'])) {
            $where[' 1>0 AND extra_data LIKE ?'] = '%"REQUEST_METHOD": "%' . $params['method'] . '%';
        }
        if (!empty($params['email'])) {
            $where[' 2>1 AND extra_data LIKE ?'] = '%"email": "%' . $params['email'] . '%';
        }
        if (!empty($params['name'])) {
            $where[' 3>2 AND extra_data LIKE ?'] = '%"name": "%' . $params['name'] . '%';
        }
        if (!empty($params['ip'])) {
            $where['4>3 AND extra_data LIKE ?'] = '%"REMOTE_ADDR": "%' . $params['ip'] . '%';
        }
        if (!empty($params['role'])) {
            $where[' 5>4 AND extra_data LIKE ?'] = '%"' . $params['role'] . '"%';
        }
        if (!empty($params['identity'])) {
            $where[' 6>5 AND extra_data LIKE ?'] = '%"identity": "%' . $params['identity'] . '%';
        }

        return $where;
    }

}