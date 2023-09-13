<?php

namespace Logger\Service;

use Laminas\Log\Logger;
use Laminas\Log\Writer\MongoDB;
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

    public function __construct(
        AccountService $accountService,
        UtilityService $utilityService,
        $config
    ) {
        $this->accountService = $accountService;
        $this->utilityService = $utilityService;
        $this->config         = $config;
    }

    public function write(string $message, array $params = []): void
    {
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
            case 'none':
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
        $logger  = new Logger();
        $logger->addWriter($writer);
        $logger->info($message, $params);
    }

    public function writeToMysql(string $message, array $params): void
    {
    }

    public function writeToFile(string $message, array $params): void
    {
    }
}