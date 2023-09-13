<?php

namespace Logger\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\Log\Formatter\Json;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Db;
use Laminas\Log\Writer\MongoDB;
use Laminas\Log\Writer\Stream;
use MongoDB\Driver\Manager;
use User\Service\AccountService;
use User\Service\UtilityService;

class LoggerService implements ServiceInterface
{
    /** @var AccountService */
    protected AccountService $accountService;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /* @var array */
    protected array $config;

    /* @var int */
    protected int $priority = Logger::INFO;

    public function __construct(
        AccountService $accountService,
        UtilityService $utilityService,
        $config
    ) {
        $this->accountService = $accountService;
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
        if (is_numeric($priority)) {
            $this->setPriority($priority);
        }

        switch ($this->config['storage']) {
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

    public function writeToMongo(string $message, array $params): void
    {
        // Set writer
        $manager = new Manager();
        $writer  = new MongoDB(
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

    public function writeToMysql(string $message, array $params): void
    {
        // Set data
        $data = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Set writer
        $db     = new Adapter($this->config['mysql']);
        $writer = new Db($db, 'log');

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, ['data' => $data]);
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
}