<?php

namespace Logger\Service;

use Club\Service\ScoreService;
use Logger\Repository\LoggerRepositoryInterface;
use mysql_xdevapi\Exception;
use User\Service\AccountService;
use function explode;
use function in_array;
use function is_object;
use function json_decode;

class LoggerService implements ServiceInterface
{

    /** @var AccountService */
    protected AccountService $accountService;

    /* @var LoggerRepositoryInterface */
    protected LoggerRepositoryInterface $logRepository;

    /* @var array */
    protected array $log;

    public function __construct(
        LoggerRepositoryInterface $logRepository,
                                  $log
    )
    {
        $this->logRepository = $logRepository;
        $this->log = $log;
    }

    public function writeLog():void
    {}

    /**
     * @param null
     *
     * @return null
     */
    public function writeTestLogger()
    {
        $this->log['logger']->info('Informational message');
        $this->log['logger']->emerg('Informational message');
        $this->logRepository->addLog(["user_id" => 0]);
    }


    public function canonizeLogger(object|array $log): array
    {
        if (empty($log)) {
            return [];
        }

        if (is_object($log)) {
            $log = [
                'id' => $log->getId(),
                'user_id' => $log->getUserId(),
                'item_id' => $log->getItemId(),
                'action' => $log->getAction(),
                'event' => $log->getEvent(),
                'type' => $log->getType(),
                'date' => $log->getDate(),
                'information' => $log->getInformation(),
                'time_create' => $log->getTimeCreate(),
                'time_update' => $log->getTimeUpdate(),
                'time_delete' => $log->getTimeDelete(),
            ];
        } else {
            $log = [
                'id' => $log['id'],
                'user_id' => $log['user_id'],
                'item_id' => $log['item_id'],
                'action' => $log['action'],
                'event' => $log['event'],
                'type' => $log['type'],
                'date' => $log['date'],
                'information' => $log['information'],
                'time_create' => $log['time_create'],
                'time_update' => $log['time_update'],
                'time_delete' => $log['time_delete'],
            ];
        }
        $log['information'] = json_decode($log['information']);
        return $log;
    }


    public function addLogger(array $log)
    {
        $this->checkOverflow();

        $information = $log['information'];
        unset($log['information']);
        $information = array_merge($information, $log);
        $log['information'] = json_encode($information);
        $log['date'] = date("l jS \of F Y h:i:s A");
        $this->logRepository->addLog($log);
    }

    public function updateLogger(array $log)
    {
        $this->logRepository->updateLog($log);
    }

    public function getLog(array $log): array
    {

        $row = $this->logRepository->getLog(['id' => 1]);
        return $this->canonizeLogger($row);
    }

    public function getAllLog(array $filter = []): array
    {

        $row = $this->logRepository->getLogsList();
        $list = [];
        foreach ($row as $log)
            $list[] = $this->canonizeLogger($log);
        return $list;
    }

    private function checkOverflow()
    {
        $count = $this->logRepository->getLogsCount();
        if ($count >= $this->log['log_overflow_size']) {
            $this->logRepository->emptyLogTable(['limit' => $this->log['log_trash_size']]);
        }
    }


}
