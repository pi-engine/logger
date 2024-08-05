<?php

namespace Logger\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\Log\Formatter\Json;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Db;
use Laminas\Log\Writer\MongoDB;
use Laminas\Log\Writer\Stream;
use Logger\Repository\LogRepositoryInterface;
use MongoDB\Driver\Manager;

class LoggerService implements ServiceInterface
{
    /** @var LogRepositoryInterface */
    protected LogRepositoryInterface $logRepository;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /* @var array */
    protected array $config;

    /* @var int */
    protected int $priority = Logger::INFO;

    /* @var string */
    protected string $tableLog = 'log_inventory';

    public function __construct(
        LogRepositoryInterface $logRepository,
        UtilityService $utilityService,
        $config
    ) {
        $this->logRepository  = $logRepository;
        $this->utilityService = $utilityService;
        $this->config         = $config;
    }

    public function setPriority($priority): void
    {
        switch ($priority) {
            case 0:
                $this->priority = Logger::EMERG;
                break;
            case 1:
                $this->priority = Logger::ALERT;
                break;
            case 2:
                $this->priority = Logger::CRIT;
                break;
            case 3:
                $this->priority = Logger::ERR;
                break;
            case 4:
                $this->priority = Logger::WARN;
                break;
            case 5:
                $this->priority = Logger::NOTICE;
                break;
            case 6:
                $this->priority = Logger::INFO;
                break;
            case 7:
                $this->priority = Logger::DEBUG;
                break;
        }
    }

    public function write(string $message, array $params = [], int $priority = null): void
    {
        // Set priority
        if (is_numeric($priority)) {
            $this->setPriority($priority);
        }

        // Save log
        $storage = $this->config['storage'] ?? 'disable';
        switch ($storage) {
            case 'mysql':
                $this->writeToMysql($message, $params);
                break;

            case 'mongodb':
                $this->writeToMongo($message, $params);
                break;

            case 'file':
                $this->writeToFile($message, $params);
                break;

            case '':
            case 'disable':
            default:
                break;
        }
    }

    public function writeToMysql(string $message, array $params): void
    {
        // Set data
        $data = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

        // Set writer
        $db     = new Adapter($this->config['mysql']);
        $writer = new Db($db, $this->tableLog);

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log(Logger::INFO, $message, [
            'time_create' => time(),
            'user_id'     => (int)$params['user_id'],
            'company_id'  => (int)$params['company_id'],
            'data'        => $data,
        ]);
    }

    public function writeToMongo(string $message, array $params): void
    {
        $manager = new Manager($this->config['mongodb']['uri']);
        $writer  = new MongoDB(
            $manager,
            $this->config['mongodb']['database'],
            $this->config['mongodb']['collection']
        );

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, $params);
    }

    public function writeToFile(string $message, array $params): void
    {
        // Set file path
        $path = sprintf('%s/%s.log', $this->config['file']['path'], date($this->config['file']['date_format']));

        // Set writer
        $formatter = new Json();
        $writer    = new Stream($path);
        $writer->setFormatter($formatter);

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, $params);
    }

    public function cleanUp(): void
    {
        $storage = $this->config['storage'] ?? 'disable';
        switch ($storage) {
            case 'mysql':
                $this->cleanUpMysql();
                break;

            case 'mongodb':
                $this->cleanUpMongo();
                break;

            case 'file':
                $this->cleanUpFile();
                break;

            case '':
            case 'disable':
            default:
                break;
        }
    }

    // ToDo: Finish it
    public function cleanUpMysql(): void
    {
    }

    // ToDo: Finish it
    public function cleanUpMongo(): void
    {
    }

    // ToDo: Finish it
    public function cleanUpFile(): void
    {
    }

    public function readInventoryLog($params): array
    {
        $listParams = $this->utilityService->paramsFraming($params);

        $inventoryList = $this->logRepository->readInventoryLog($listParams);
        $list          = $this->utilityService->inventoryLogListCanonize($inventoryList);
        $count         = $this->logRepository->getInventoryLogCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => (int)$listParams['limit'],
                    'page'  => (int)$listParams['page'],
                ],
                'filters'   => null,
            ],
            'error'  => [],
        ];
    }

    public function addUserLog(string $state, array $params): void
    {
        $params = [
            'user_id'     => (int)($params['account']['id'] ?? 0),
            'operator_id' => (int)(isset($params['operator']) ? !empty($params['operator']) ? $params['operator']['id'] ?? 0 : 0 : 0),
            'time_create' => time(),
            'state'       => $state,
            'information' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];

        $this->logRepository->addUser($params);
    }

    public function getUserLog($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['time_create DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        if (isset($params['state']) && !empty($params['state'])) {
            $listParams['state'] = $params['state'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['ip']) && !empty($params['ip'])) {
            $listParams['ip'] = $params['ip'];
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $listParams['method'] = $params['method'];
        }
        if (isset($params['role']) && !empty($params['role'])) {
            $listParams['role'] = $params['role'];
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            if (is_string($params['user_id']) && str_contains($params['user_id'], ',')) {
                $listParams['user_id'] = explode(',', $params['user_id']);
            } else {
                $listParams['user_id'] = $params['user_id'];
            }
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = strtotime(
                ($params['data_from']) != null
                    ? sprintf('%s 00:00:00', $params['data_from'])
                    : sprintf('%s 00:00:00', date('Y-m-d', strtotime('-1 month')))
            );
        }

        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = strtotime(
                ($params['data_to']) != null
                    ? sprintf('%s 00:00:00', $params['data_to'])
                    : sprintf('%s 23:59:59', date('Y-m-d'))
            );
        }

        // Get list
        $list   = [];
        $rowSet = $this->logRepository->getUserList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->utilityService->canonizeUserLog($row);
        }

        // Get count
        $count = $this->logRepository->getUserCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }
}