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
use Logger\Service\UtilityService;

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
        UtilityService         $utilityService,
                               $config
    )
    {
        $this->logRepository = $logRepository;
        $this->utilityService = $utilityService;
        $this->config = $config;
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
        $data = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Set writer
        $db = new Adapter($this->config['mysql']);
        $writer = new Db($db, $this->tableLog);

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, ['data' => $data]);
    }

    public function writeToMongo(string $message, array $params): void
    {
        // Set writer
        $manager = new Manager();
        $writer = new MongoDB(
            $manager,
            $this->config['mongodb']['database'],
            $this->config['mongodb']['collection'],
            $this->config['mongodb']['saveOptions']
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
        $writer = new Stream($path);
        $writer->setFormatter($formatter);

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, $params);
    }

    public function addUserLog(string $state, array $params): void
    {
        $params = [
            'user_id' => $params['account']['id'],
            'time_create' => time(),
            'state' => $state,
            'information' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];

        $this->logRepository->addUser($params);
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

        $inventoryObjectList = $this->logRepository->readInventoryLog($listParams);
        $list =  $this->utilityService->inventoryLogListCanonize($inventoryObjectList);
        $count = $this->logRepository->getInventoryLogCount($listParams);
        return [
            'result' => true,
            'data' => [
                'list' => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $listParams['limit'],
                    'page' => $listParams['page'],
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }
}