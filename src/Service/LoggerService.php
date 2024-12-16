<?php

namespace Pi\Logger\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\Log\Formatter\Json;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Db;
use Laminas\Log\Writer\MongoDB;
use Laminas\Log\Writer\Stream;
use MongoDB\Driver\Manager;
use Pi\Core\Service\UtilityService;
use Pi\Logger\Repository\LogRepositoryInterface;

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
    protected string $tableLog = 'logger_system';

    protected array $forbiddenParams
        = [
            'credential',
            'credentialColumn',
            'token',
            'access_token',
            'refresh_token',
            'token_payload',
            'permission',
        ];

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
        // Clean up
        $params = $this->cleanupForbiddenKeys($params);

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

        // Check and cleanup
        if (isset($this->config['cleanup']) && (bool)$this->config['cleanup'] === true) {
            $this->cleanUpMysql();
        }
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

    public function cleanUpMysql(): void
    {
        $limitation = $this->config['limitation'] ?? 10000;
        $this->logRepository->cleanupSystemLog($limitation);
    }

    public function cleanupForbiddenKeys(array $params): array
    {
        foreach ($params as $key => $value) {
            if (in_array($key, $this->forbiddenParams)) {
                unset($params[$key]);
            } elseif (is_array($value)) {
                $params[$key] = $this->cleanupForbiddenKeys($value);
            }
        }

        return $params;
    }

    public function getSystemLog($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['log.extra_time_create DESC', 'log.id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
            'page'   => $page,
        ];

        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['priority']) && !empty($params['priority'])) {
            $listParams['priority'] = $params['priority'];
        }
        if (isset($params['priorityName']) && !empty($params['priorityName'])) {
            $listParams['priorityName'] = $params['priorityName'];
        }
        if (isset($params['message']) && !empty($params['message'])) {
            $listParams['message'] = $params['message'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = strtotime(sprintf('%s 00:00:00', $params['data_from']));
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = strtotime(sprintf('%s 00:00:00', $params['data_to']));
        }

        // Set for extra_data
        if (isset($params['ip']) && !empty($params['ip'])) {
            $listParams['extra_data']['ip'] = $params['ip'];
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $listParams['extra_data']['request.method'] = $params['method'];
        }
        if (isset($params['target']) && !empty($params['target'])) {
            $listParams['extra_data']['request.target'] = $params['target'];
        }
        if (isset($params['module']) && !empty($params['module'])) {
            $listParams['extra_data']['route.module'] = $params['module'];
        }
        if (isset($params['section']) && !empty($params['section'])) {
            $listParams['extra_data']['route.section'] = $params['section'];
        }
        if (isset($params['package']) && !empty($params['package'])) {
            $listParams['extra_data']['route.package'] = $params['package'];
        }
        if (isset($params['handler']) && !empty($params['handler'])) {
            $listParams['extra_data']['route.handler'] = $params['handler'];
        }
        if (isset($params['permissions']) && !empty($params['permissions'])) {
            $listParams['extra_data']['route.permissions'] = $params['permissions'];
        }

        $list          = [];
        $systemList = $this->logRepository->getSystemLogList($listParams);
        foreach ($systemList as $object) {
            $list[] = $this->canonizeSystemLog($object);
        }

        // Get count
        $count = $this->logRepository->getSystemLogCount($listParams);

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

    public function addUserLog(string $state, array $params): void
    {
        $params = [
            'user_id'     => (int)($params['account']['id'] ?? 0),
            'operator_id' => (int)(isset($params['operator']) ? !empty($params['operator']) ? $params['operator']['id'] ?? 0 : 0 : 0),
            'time_create' => time(),
            'state'       => $state,
            'information' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];

        $this->logRepository->addUserLog($params);
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

        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['operator_id']) && !empty($params['operator_id'])) {
            $listParams['operator_id'] = $params['operator_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $listParams['state'] = $params['state'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = strtotime(sprintf('%s 00:00:00', $params['data_from']));
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = strtotime(sprintf('%s 00:00:00', $params['data_to']));
        }

        // Set for information
        if (isset($params['ip']) && !empty($params['ip'])) {
            $listParams['information']['ip'] = $params['ip'];
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $listParams['information']['method'] = $params['method'];
        }

        // Get list
        $list   = [];
        $rowSet = $this->logRepository->getUserLogList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeUserLog($row);
        }

        // Get count
        $count = $this->logRepository->getUserLogCount($listParams);

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

    public function canonizeSystemLog($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id'                => (int)$object->getId(),
                'timestamp'         => $object->getTimestamp(),
                'priority'          => $object->getPriority(),
                'priority_name'     => $object->getPriorityName(),
                'message'           => $object->getMessage(),
                'extra_data'        => $object->getExtraData(),
                'extra_time_create' => $object->getExtraTimeCreate(),
                'extra_user_id'     => $object->getExtraUserId(),
                'extra_company_id'  => $object->getExtraCompanyId(),
                'user_identity'     => $object->getUserIdentity(),
                'user_name'         => $object->getUserName(),
                'user_email'        => $object->getUserEmail(),
                'user_mobile'       => $object->getUserMobile(),
            ];
        } else {
            $object = [
                'id'                => (int)$object['id'],
                'timestamp'         => $object['timestamp'],
                'priority'          => $object['priority'],
                'priority_name'     => $object['priorityName'],
                'message'           => $object['message'],
                'extra_data'        => $object['extra_data'],
                'extra_time_create' => $object['extra_time_create'],
                'extra_user_id'     => $object['extra_user_id'],
                'extra_company_id'  => $object['extra_company_id'],
                'user_identity'     => $object['user_identity'],
                'user_name'         => $object['user_name'],
                'user_email'        => $object['user_email'],
                'user_mobile'       => $object['user_mobile'],
            ];
        }

        // Set time
        $object['time_create_view'] = $this->utilityService->date($object['extra_time_create']);

        // Set information
        $object['extra_data'] = !empty($object['extra_data']) ? json_decode($object['extra_data'], true) : [];

        // Set output params
        $object['user_id']          = $object['extra_user_id'];
        $object['company_id']       = $object['extra_company_id'];
        $object['company_title']    = $object['extra_data']['request']['attributes']['company_authorization']['company']['title'] ?? null;
        $object['ip']               = $object['extra_data']['ip'] ?? null;
        $object['title']            = $object['extra_data']['route']['title'] ?? null;
        $object['method']           = $object['extra_data']['request']['method'] ?? null;
        $object['target']           = $object['extra_data']['request']['target'] ?? null;
        $object['section']          = $object['extra_data']['route']['section'] ?? null;
        $object['module']           = $object['extra_data']['route']['module'] ?? null;
        $object['package']          = $object['extra_data']['route']['package'] ?? null;
        $object['handler']          = $object['extra_data']['route']['handler'] ?? null;
        $object['package_id']       = $object['extra_data']['request']['attributes']['company_authorization']['package']['id'] ?? null;
        $object['package_title']    = $object['extra_data']['request']['attributes']['company_authorization']['package']['title'] ?? null;
        $object['request_body']     = $object['extra_data']['request']['parsedBody'] ?? null;
        $object['security_stream']  = $object['extra_data']['request']['attributes']['security_stream'] ?? null;

        unset($object['extra_data']);
        unset($object['extra_user_id']);
        unset($object['extra_company_id']);
        unset($object['timestamp']);

        return $object;
    }

    public function canonizeUserLog($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id'                => (int)$object->getId(),
                'user_id'           => $object->getUserId(),
                'user_identity'     => $object->getUserIdentity(),
                'user_name'         => $object->getUserName(),
                'user_email'        => $object->getUserEmail(),
                'user_mobile'       => $object->getUserMobile(),
                'operator_id'       => $object->getOperatorId(),
                'operator_identity' => $object->getOperatorIdentity(),
                'operator_name'     => $object->getOperatorName(),
                'operator_email'    => $object->getOperatorEmail(),
                'operator_mobile'   => $object->getOperatorMobile(),
                'time_create'       => $object->getTimeCreate(),
                'state'             => $object->getState(),
                'information'       => $object->getInformation(),
            ];
        } else {
            $object = [
                'id'                => (int)$object['id'],
                'user_id'           => $object['user_id'],
                'user_identity'     => $object['user_identity'],
                'user_name'         => $object['user_name'],
                'user_email'        => $object['user_email'],
                'user_mobile'       => $object['user_mobile'],
                'operator_id'       => $object['operator_id'],
                'operator_identity' => $object['operator_identity'],
                'operator_name'     => $object['operator_name'],
                'operator_email'    => $object['operator_email'],
                'operator_mobile'   => $object['operator_mobile'],
                'time_create'       => $object['time_create'],
                'state'             => $object['state'],
                'information'       => $object['information'],
            ];
        }

        // Set data
        $object['time_create_view'] = $this->utilityService->date($object['time_create']);

        // Set information
        $object['information'] = json_decode($object['information'], true);

        // Unset not used data
        unset($object['information']['params']['serverParams']);
        unset($object['information']['request']['security_stream']);

        return $object;
    }
}