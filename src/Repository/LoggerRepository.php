<?php

namespace Logger\Repository;

use Logger\Model\Logger;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Hydrator\HydratorInterface;
use RuntimeException;
use function sprintf;


class LoggerRepository implements LoggerRepositoryInterface
{
    /**
     * Log Table name
     *
     * @var string
     */
    private string $tableLog = 'logger_log';


    /**
     * @var AdapterInterface
     */
    private AdapterInterface $db;

    /**
     * @var Logger
     */
    private Logger $logPrototype;


    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;


    public function __construct(
        AdapterInterface  $db,
        HydratorInterface $hydrator,
        Logger            $logPrototype,
    )
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->logPrototype = $logPrototype;
    }


    /**
     * @param string $parameter
     * @param string $type
     *
     * @return object|array
     */
    public function getLog($params, string $type = "object"): object|array
    {
        $where = [];
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $params['id'];
        }
        if (isset($params['item_id']) && !empty($params['item_id'])) {
            $where['item_id'] = $params['item_id'];
        }

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }

        if (isset($params['action']) && !empty($params['action'])) {
            $where['action'] = $params['action'];
        }

        if (isset($params['time_delete'])) {
            $where['time_delete'] = $params['time_delete'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableLog)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->logPrototype);
        $resultSet->initialize($result);
        $item = $resultSet->current();

        if (!$item) {
            return [];
        }

        return $item;
    }


    /**
     * @param array $params
     *
     * @return array|object
     */
    public function addLog(array $params): object|array
    {
        $insert = new Insert($this->tableLog);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
        $id = $result->getGeneratedValue();
        return $this->getLog(["id" => $id], "object");
    }

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function updateLog(array $params): object|array
    {
        $update = new Update($this->tableLog);
        $update->set($params);
        if (isset($params["id"]))
            $update->where(['id' => $params["id"]]);
        if (isset($params["action"]))
            $update->where(['action' => $params["action"]]);
        if (isset($params["user_id"]))
            $update->where(['user_id' => $params["user_id"]]);
        if (isset($params["item_id"]))
            $update->where(['item_id' => $params["item_id"]]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
        return $this->getLog($params, "object");
    }

    public function getLogsList()
    {
        $where = [];

        $order = ['id DESC'];

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableLog)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->logPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getLogsCount(): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where = [];


        $sql = new Sql($this->db);
        $select = $sql->select($this->tableLog)->columns($columns)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function emptyLogTable(array $array)
    {
        // Delete from role table
        $delete = new Delete($this->tableLog);
        $delete->where([" 1 Limit " . $array['limit'] . "  "]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }


    }
}
