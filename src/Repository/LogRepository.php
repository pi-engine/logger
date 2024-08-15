<?php

namespace Logger\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Hydrator\HydratorInterface;
use Logger\Model\System;
use Logger\Model\User;
use RuntimeException;

class LogRepository implements LogRepositoryInterface
{
    private string $tableSystem = 'logger_system';

    private string $tableUser = 'logger_user';

    private string $tableAccount = 'user_account';

    private AdapterInterface $db;

    private System $systemPrototype;

    private User $userPrototype;

    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        System $systemPrototype,
        User $userPrototype,
    ) {
        $this->db                 = $db;
        $this->hydrator           = $hydrator;
        $this->systemPrototype = $systemPrototype;
        $this->userPrototype      = $userPrototype;
    }

    public function addUserLog(array $params = []): void
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

    public function getUserLogList(array $params = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['operator_id']) && !empty($params['operator_id'])) {
            $where['log.operator_id'] = $params['operator_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }
        if (!empty($params['data_from'])) {
            $where['log.time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.time_create <= ?'] = $params['data_to'];
        }
        if (isset($params['information']) && !empty($params['information']) && is_array($params['information'])) {
            foreach ($params['information'] as $key => $value) {
                $where[] = new Expression(sprintf("JSON_EXTRACT(log.information, '$.%s') = '%s'", $key, $value));
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableUser];
        $select = $sql->select()->from($from)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getUserLogCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['operator_id']) && !empty($params['operator_id'])) {
            $where['log.operator_id'] = $params['operator_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }
        if (!empty($params['data_from'])) {
            $where['log.time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.time_create <= ?'] = $params['data_to'];
        }
        if (isset($params['information']) && !empty($params['information']) && is_array($params['information'])) {
            foreach ($params['information'] as $key => $value) {
                $where[] = new Expression(sprintf("JSON_EXTRACT(log.information, '$.%s') = '%s'", $key, $value));
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableUser];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function getSystemLogList(array $params = []): HydratingResultSet|array
    {
        $where   = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['priority']) & !empty($params['priority'])) {
            $where['log.priority'] = $params['priority'];
        }
        if (isset($params['priorityName']) & !empty($params['priorityName'])) {
            $where['log.priorityName like ?'] = '%' . $params['priorityName'] . '%';
        }
        if (isset($params['message']) & !empty($params['message'])) {
            $where['log.message like ?'] = '%' . $params['message'] . '%';
        }
        if (isset($params['user_id']) & !empty($params['user_id'])) {
            $where['log.extra_user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) & !empty($params['company_id'])) {
            $where['log.extra_company_id'] = $params['company_id'];
        }
        if (!empty($params['data_from'])) {
            $where['log.extra_time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.extra_time_create <= ?'] = $params['data_to'];
        }
        if (isset($params['extra_data']) && !empty($params['extra_data']) && is_array($params['extra_data'])) {
            foreach ($params['extra_data'] as $key => $value) {
                $where[] = new Expression(sprintf("JSON_EXTRACT(log.extra_data, '$.%s') = '%s'", $key, $value));
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableSystem];
        $select = $sql->select()->from($from)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
        $select->join(
            ['account' => $this->tableAccount],
            'log.extra_user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->systemPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getSystemLogCount(array $params = []): int
    {
        $columns = ['count' => new Expression('count(*)')];
        $where   = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['priority']) & !empty($params['priority'])) {
            $where['log.priority'] = $params['priority'];
        }
        if (isset($params['priorityName']) & !empty($params['priorityName'])) {
            $where['log.priorityName like ?'] = '%' . $params['priorityName'] . '%';
        }
        if (isset($params['message']) & !empty($params['message'])) {
            $where['log.message like ?'] = '%' . $params['message'] . '%';
        }
        if (isset($params['user_id']) & !empty($params['user_id'])) {
            $where['log.extra_user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) & !empty($params['company_id'])) {
            $where['log.extra_company_id'] = $params['company_id'];
        }
        if (!empty($params['data_from'])) {
            $where['log.extra_time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.extra_time_create <= ?'] = $params['data_to'];
        }
        if (isset($params['extra_data']) && !empty($params['extra_data']) && is_array($params['extra_data'])) {
            foreach ($params['extra_data'] as $key => $value) {
                $where[] = new Expression(sprintf("JSON_EXTRACT(log.extra_data, '$.%s') = '%s'", $key, $value));
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableSystem];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'log.extra_user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();
        return (int)$row['count'];
    }

    public function cleanupSystemLog(int $limitation = 10000): void
    {
        // Set columns
        $columns = ['count' => new Expression('count(*)')];

        // Get count
        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableSystem)->columns($columns);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();
        $count     = (int)$row['count'];

        // Check count and delete
        if ($count > $limitation) {
            // Get first available id
            $select    = $sql->select($this->tableSystem)->columns(['id'])->order('id ASC')->limit(1);
            $statement = $sql->prepareStatementForSqlObject($select);
            $row       = $statement->execute()->current();

            // Check id and delete
            if (isset($row['id']) && (int)$row['id'] > 0) {
                // Do Delete
                $where     = ['id' => (int)$row['id']];
                $delete    = $sql->delete($this->tableSystem)->where($where);
                $statement = $sql->prepareStatementForSqlObject($delete);
                $result    = $statement->execute();

                if (!$result instanceof ResultInterface) {
                    throw new RuntimeException(
                        'Database error occurred during update operation'
                    );
                }
            }
        }
    }
}